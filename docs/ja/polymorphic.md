# ポリモーフィックな添付ファイルモデルを使ったファイルの保存

複数のモデルで複数のファイルのアップロードを保存したいが、データベースが正規化されているので複数の
テーブルは使いたくない、という場合があります。たとえば、ギャラリー用に多数の画像を持てる `Post`
モデルと、多数の動画を持つ `Message` モデルがあるとします。このような場合は `Attachment` モデルを
使います。

Post hasMany Attachment

`Attachment` モデルには、次のようなデータベーススキーマを使えます。

```sql
CREATE table attachments (
    `id` int(10) unsigned NOT NULL auto_increment,
    `model` varchar(20) NOT NULL,
    `foreign_key` int(11) NOT NULL,
    `name` varchar(32) NOT NULL,
    `attachment` varchar(255) NOT NULL,
    `dir` varchar(255) DEFAULT NULL,
    `type` varchar(255) DEFAULT NULL,
    `size` int(11) DEFAULT 0,
    `active` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`)
);
```

これで、添付ファイルのレコードは名前を持ち、その場で有効・無効を切り替えられるようになります。
このスキーマは単なる例であり、こうした機能はアプリケーションの中で実装する必要があります。

`attachments` テーブルを作成したら、次のモデルを作成します。

```php
class Attachment extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'attachment' => [
                'thumbnailSizes' => [
                    'xvga' => '1024x768',
                    'vga' => '640x480',
                    'thumb' => '80x80',
                ],
            ],
        ],
    ];

    public $belongsTo = [
        'Post' => [
            'className' => 'Post',
            'foreignKey' => 'foreign_key',
        ],
        'Message' => [
            'className' => 'Message',
            'foreignKey' => 'foreign_key',
        ],
    ];
}
```

`Post` モデルにも、正しい逆方向のリレーションを作成する必要があります。

```php
class Post extends AppModel
{
    public $hasMany = [
        'Image' => [
            'className' => 'Attachment',
            'foreignKey' => 'foreign_key',
            'conditions' => [
                'Image.model' => 'Post',
            ],
        ],
    ];
}
```

ここで重要なのは、`Post` モデルの `Attachment` モデルへのリレーションに、`Image.model` が `Post`
でなければならないという条件が付いていることです。`model` フィールドには `Post`、または添付先にしたい
モデルを忘れずに設定してください。そうしないと、find のクエリを実行したときに、正しくない
リレーションの結果が返されることがあります。

`Message` モデルにも同じようなリレーションが必要です。

```php
class Message extends AppModel
{
    public $hasMany = [
        'Video' => [
            'className' => 'Attachment',
            'foreignKey' => 'foreign_key',
            'conditions' => [
                'Video.model' => 'Message',
            ],
        ],
    ];
}
```

モデルの準備ができたので、コントローラーに適切なアクションを作成します。簡潔にするために、
Post モデルについてだけ説明します。

```php
class PostsController extends AppController
{
    public $components = ['Flash'];

    /* the rest of your controller here */
    public function add()
    {
        if ($this->request->is('post')) {
            try {
                $this->Post->createWithAttachments($this->request->data);
                $this->Flash->success(__('The post has been saved'));
            } catch (Exception $e) {
                $this->Flash->error($e->getMessage());
            }
        }
    }
}
```

上の例では、`Post` モデルの独自の `createWithAttachments` メソッドを呼び出しています。これで、Post を
作成する処理を一か所にまとめられます。このメソッドは次のとおりです。

```php
class Post extends AppModel
{
    /* the rest of your model here */

    public function createWithAttachments($data)
    {
        // Sanitize your images before adding them
        $images = [];
        if (!empty($data['Image'][0])) {
            foreach ($data['Image'] as $i => $image) {
                if (is_array($data['Image'][$i])) {
                    // Force setting the `model` field to this model
                    $image['model'] = 'Post';

                    // Unset the foreign_key if the user tries to specify it
                    if (isset($image['foreign_key'])) {
                        unset($image['foreign_key']);
                    }

                    $images[] = $image;
                }
            }
        }
        $data['Image'] = $images;

        // Try to save the data using Model::saveAll()
        $this->create();
        if ($this->saveAll($data)) {
            return true;
        }

        // Throw an exception for the controller
        throw new Exception(__('This post could not be saved. Please try again'));
    }
}
```

上のモデルのメソッドは次のことを行います。

- 有効な画像だけを保存しようとするようにします
- foreign_key が指定されていない状態にします。これで saveAll が正しく関連付けられます
- model フィールドを `Post` にします

これで準備ができたので、あとはコントローラーのビューが必要です。`templates/Posts/add.ctp`
(レガシーレイアウトでは `app/View/Posts/add.ctp`) のビューの例は次のとおりです (この例に必要のない
フィールドは省略しています)。

```php
<?php
echo $this->Form->create('Post', ['type' => 'file']);
echo $this->Form->input('Image.0.attachment', ['type' => 'file', 'label' => 'Image']);
echo $this->Form->input('Image.0.model', ['type' => 'hidden', 'value' => 'Post']);
echo $this->Form->end(__('Add'));
```

一つ重要な点として、`Attachment` モデルを `Attachment` ではなく `Image` として参照していることに
気付くでしょう。最初に `Attachment` と `Post` の間の `$hasMany` リレーションを指定したときに、
`Attachment` に `Image` というエイリアスを付けました。これは、多くのポリモーフィックなモデルが互いに
関連している場合に、CakePHP の ORM がモデルのデータを正しく参照するための一種の *ヒント* として
必要です。

また、`Model.{n}.field` という記法を使っているので、Post に複数の添付ファイルのレコードを追加できます。
これは、この例で使っている `$hasMany` リレーションに必要です。

ここまでの準備がすべて整えば、ポリモーフィックなアップロードが動作します。

これはファイルのアップロードを表現する唯一の方法ではありませんが、参考としてここに記載しています。
