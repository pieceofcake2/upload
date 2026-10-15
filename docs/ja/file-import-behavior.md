# FileImportBehavior

`FileImportBehavior` を使うと、ディスクから直接ファイルをインポートできます。ファイルシステム上に
すでにあるディレクトリからインポートするのに便利です。

`UploadBehavior` を継承しており、同じオプションを受け付けます。

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.FileImport' => [
            'photo',
        ],
    ];
}
```

ファイルはアップロードされたファイルと同じ形式で、`tmp_name` にディスク上のファイルを設定して渡します。
フォームからアップロードされたのではないファイルは、`rename()` でアップロード先のパスに
(コピーではなく) 移動されます。

```php
$file = '/path/to/photo.jpg';
$this->User->save([
    'User' => [
        'photo' => [
            'name' => basename($file),
            'type' => mime_content_type($file),
            'tmp_name' => $file,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($file),
        ],
    ],
]);
```

`UploadBehavior` と同じように、フィールドの値として URL を指定すると、ダウンロードして保存されます
([フォームを使わずにプログラムでファイルを取得する](examples.md#programmatic-file-retrieval-without-a-form) を参照)。
