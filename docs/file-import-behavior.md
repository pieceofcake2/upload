# FileImportBehavior

`FileImportBehavior` may be used to import files directly from the disk. This is useful in importing from a directory already on the filesystem.

It extends `UploadBehavior` and takes the same options:

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

Pass the file in the same format as an uploaded file, with `tmp_name` set to the file on disk.
A file that was not uploaded through a form is moved (not copied) to the upload path with `rename()`:

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

Like `UploadBehavior`, a URL given as the field value is downloaded and saved (see
[Programmatic File Retrieval without a Form](examples.md#programmatic-file-retrieval-without-a-form)).
