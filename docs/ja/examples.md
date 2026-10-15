# 例

<a id="basic-example"></a>

## 基本的な例

> 注意: Upload ビヘイビアは、コアの Translate ビヘイビアより *前に* 定義したほうがよいかもしれません。この二つは互いに衝突することが知られています。

```sql
CREATE table users (
    id int(10) unsigned NOT NULL auto_increment,
    username varchar(20) NOT NULL,
    photo varchar(255)
);
```

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'photo',
        ],
    ];
}
```

```php
<?= $this->Form->create('User', ['type' => 'file']) ?>
<?= $this->Form->input('User.username') ?>
<?= $this->Form->input('User.photo', ['type' => 'file']) ?>
<?= $this->Form->end() ?>
```

上の設定では、アップロードしたファイルを削除できません。削除できるようにするには、次のように
ファイルのディレクトリを保存するフィールドを追加する必要があります。

```sql
CREATE table users (
    `id` int(10) unsigned NOT NULL auto_increment,
    `username` varchar(20) NOT NULL,
    `photo` varchar(255) DEFAULT NULL,
    `photo_dir` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
);
```

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'photo' => [
                'fields' => [
                    'dir' => 'photo_dir',
                ],
            ],
        ],
    ];
}
```

上の例では、photo には、フォームの file input によるファイルのアップロード、text input による
(URL からの) ファイルの取得、またはコントローラーからプログラムで URL を指定したファイルの取得の
いずれも使えます。

<a id="file-upload-example"></a>

### ファイルのアップロードの例

```php
<?= $this->Form->create('User', ['type' => 'file']) ?>
    <?= $this->Form->input('User.username') ?>
    <?= $this->Form->input('User.photo', ['type' => 'file']) ?>
    <?= $this->Form->input('User.photo_dir', ['type' => 'hidden']) ?>
<?= $this->Form->end() ?>
```

<a id="file-grabbing-via-form-example"></a>

### フォームからのファイルの取得の例

text input にファイルの URL を入力します。

```php
<?= $this->Form->create('User', ['type' => 'file']) ?>
    <?= $this->Form->input('User.username') ?>
    <?= $this->Form->input('User.photo', ['type' => 'text']) ?>
    <?= $this->Form->input('User.photo_dir', ['type' => 'hidden']) ?>
<?= $this->Form->end() ?>
```

<a id="programmatic-file-retrieval-without-a-form"></a>

### フォームを使わずにプログラムでファイルを取得する

```php
$this->User->set(['photo' => $image_url]);
$this->User->save();
```

<a id="thumbnail-creation"></a>

### サムネイルの作成

サムネイルは自動的には作成されません。作成するには、サムネイルのサイズを定義する必要があります。
注意: デフォルトではサムネイルは imagick で生成されます。GD を使いたい場合は thumbnailMethod 属性を
設定する必要があります。例: `'thumbnailMethod' => 'php'`。

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'photo' => [
                'thumbnailSizes' => [
                    'xvga' => '1024x768',
                    'vga' => '640x480',
                    'thumb' => '80x80',
                ],
            ],
        ],
    ];
}
```

<a id="displaying-links-to-files-in-your-view"></a>

### ビューでファイルへのリンクを表示する

ファイルをアップロードしたら、パスを指定し、データベースのファイルの情報を使って、`HtmlHelper` で
リンクを作成できます。

この例では、モデル `Example` で [デフォルトのビヘイビアの設定](configuration.md) を使っています。

```php
$exampleData = [
    'Example' => [
        'image' => 'imageFile.jpg',
        'dir' => '7',
    ],
];

echo $this->Html->link('../files/example/image/' . $exampleData['Example']['dir'] . '/' . $exampleData['Example']['image']);
```

アプリケーションでサムネイルを設定している場合は、ファイル名の前にそのサムネイルの名前を付けるだけです。

```php
echo $this->Html->link('../files/example/image/' . $exampleData['Example']['dir'] . '/thumb_' . $exampleData['Example']['image']);
```

<a id="uploading-multiple-files"></a>

## 複数のファイルのアップロード

一つのレコードに複数のファイルを添付することもできます。

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'resume',
            'photo' => [
                'fields' => [
                    'dir' => 'profile_dir',
                ],
            ],
        ],
    ];
}
```

`Upload.Upload` の配列の各キーはフィールド名で、**それぞれ独自の設定を持つ** ことができます。
たとえば、ファイルのパスを保存するフィールドを別々にしたい場合は次のようにします。

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'resume' => [
                'fields' => [
                    'dir' => 'resume_dir',
                    'type' => 'resume_type',
                    'size' => 'resume_size',
                ],
            ],
            'photo' => [
                'fields' => [
                    'dir' => 'photo_dir',
                    'type' => 'photo_type',
                    'size' => 'photo_size',
                ],
            ],
        ],
    ];
}
```

このプラグインには、リクエストごとにアップロードできるファイルの数の制限はありませんが、
ユーザーが他のリクエストをブロックできてしまう余地を減らすために、数は少なく抑えるべきです。

数がわからない、または多数のアップロードをモデルに追加したい場合は、
[ポリモーフィックな添付ファイル](polymorphic.md) を使うことを検討してください。

<a id="remove-a-current-file-without-deleting-the-entire-record"></a>

## レコード全体を削除せずに現在のファイルを削除する

モデルのレコード全体を削除せずに、写真やアップロードしたファイルだけを削除したい場合があります。
その場合は次のコードを使います。

```php
echo $this->Form->create('Model', ['type' => 'file']);
echo $this->Form->input('Model.file.remove', ['type' => 'checkbox', 'label' => 'Remove existing file']);
```

<a id="saving-two-uploads-into-different-folders"></a>

## 二つのアップロードを別々のフォルダーに保存する

複数のファイルをアップロードし、それぞれを別のフォルダーに保存したい場合があります。これは実はとても
簡単です。*ファイルごとに* ビヘイビアの設定を使うだけで、パスを変更できます。
[先にプラグインをインストールしておく](README.md#installation) のを忘れないでください。

この例では、ユーザーの写真と、たとえばそのユーザーの車の写真をアップロードしたいとします。
簡単にするために、これらのファイルは `User` モデルだけに保存するものとします。

> 注意: 各フィールドがそれぞれ独自の設定を持てることに注意してください。

```php
// src/Model/User.php (legacy layout: app/Model/User.php)
public $actsAs = [
    'Upload.Upload' => [
        // The name of the field in our database, so this is `users.avatar`
        'avatar' => [
            // Here we can define the rootDir, which is the root of the application, usually an absolute path to your project
            'rootDir' => ROOT,
            // The path pattern that we want to use to save our file where {DS} is the directory separator
            // and the {ROOT}, {model} and {field} tokens are replaced with their matching values
            'path' => '{ROOT}{DS}webroot{DS}files{DS}{model}{DS}{field}{DS}',
            'fields' => [
                // It's always helpful to save the directory our files are in, just in case
                'dir' => 'image_dir',
            ],
        ],
        'car' => [
            // Here we have changed the path, so our images will now be in a different folder
            'path' => '{ROOT}{DS}webroot{DS}files{DS}cars{DS}',
        ],
    ],
];
```

<a id="changing-the-upload-path-dynamically"></a>

## アップロード先のパスを動的に変更する

アップロード先のパスを動的に変更する必要がある場合は、モデルでビヘイビアの設定を変更します。
たとえば `beforeSave()` などのモデルのコールバックの中で行います。

```php
// src/Model/User.php (legacy layout: app/Model/User.php)
$this->Behaviors->Upload->settings['field']['path'] = $newPath;
```
