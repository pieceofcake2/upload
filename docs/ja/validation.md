# バリデーションルール

デフォルトでは、モデルにバリデーションルールは設定されません。必要なルールはそれぞれ明示的に設定する必要があります。
PHP のアップロードエラーに関係しないルールは設定できますが、設定しない場合はビヘイビアの設定が使われます。

<a id="isunderphpsizelimit"></a>

## isUnderPhpSizeLimit

ファイルが PHP で指定された最大ファイルサイズを超えていないことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isUnderPhpSizeLimit',
        'message' => 'File exceeds upload filesize limit',
    ],
];
```

<a id="isunderformsizelimit"></a>

## isUnderFormSizeLimit

ファイルが HTML フォームで指定された最大ファイルサイズを超えていないことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isUnderFormSizeLimit',
        'message' => 'File exceeds form upload filesize limit',
    ],
];
```

<a id="iscompletedupload"></a>

## isCompletedUpload

ファイルが完全にアップロードされたことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isCompletedUpload',
        'message' => 'File was not successfully uploaded',
    ],
];
```

<a id="isfileupload"></a>

## isFileUpload

ファイルがアップロードされたことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isFileUpload',
        'message' => 'File was missing from submission',
    ],
];
```

<a id="isfileuploadorhasexistingvalue"></a>

## isFileUploadOrHasExistingValue

ファイルがアップロードされたか、データベースの既存の値が空でないことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isFileUploadOrHasExistingValue',
        'message' => 'File was missing from submission',
    ],
];
```

<a id="tempdirexists"></a>

## tempDirExists

PHP の一時ディレクトリが存在することをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'tempDirExists',
        'message' => 'The system temporary directory is missing',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['tempDirExists', false],
        'message' => 'The system temporary directory is missing',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="issuccessfulwrite"></a>

## isSuccessfulWrite

ファイルがサーバーに正常に書き込まれたことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'isSuccessfulWrite',
        'message' => 'File was unsuccessfully written to the server',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isSuccessfulWrite', false],
        'message' => 'File was unsuccessfully written to the server',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="nophpextensionerrors"></a>

## noPhpExtensionErrors

PHP の拡張モジュールがエラーを起こさなかったことをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => 'noPhpExtensionErrors',
        'message' => 'File was not uploaded because of a faulty PHP extension',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['noPhpExtensionErrors', false],
        'message' => 'File was not uploaded because of a faulty PHP extension',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isvalidmimetype"></a>

## isValidMimeType

ファイルが有効な MIME タイプであることをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidMimeType', ['application/pdf', 'image/png']],
        'message' => 'File is not a pdf or png',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidMimeType', ['application/pdf', 'image/png'], false],
        'message' => 'File is not a pdf or png',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="iswritable"></a>

## isWritable

アップロード先のディレクトリが書き込み可能であることをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => ['isWritable'],
        'message' => 'File upload directory was not writable',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isWritable', false],
        'message' => 'File upload directory was not writable',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isvaliddir"></a>

## isValidDir

アップロード先のディレクトリが存在することをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidDir'],
        'message' => 'File upload directory does not exist',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidDir', false],
        'message' => 'File upload directory does not exist',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isbelowmaxsize"></a>

## isBelowMaxSize

ファイルがアップロードの最大ファイルサイズを下回っていることをチェックします (バイト単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxSize', 1024],
        'message' => 'File is larger than the maximum filesize',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxSize', 1024, false],
        'message' => 'File is larger than the maximum filesize',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isaboveminsize"></a>

## isAboveMinSize

ファイルがアップロードの最小ファイルサイズを上回っていることをチェックします (バイト単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinSize', 1024],
        'message' => 'File is below the mimimum filesize',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinSize', 1024, false],
        'message' => 'File is below the mimimum filesize',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isvalidextension"></a>

## isValidExtension

ファイルが有効な拡張子を持っていることをチェックします。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidExtension', ['pdf', 'png', 'txt']],
        'message' => 'File does not have a pdf, png, or txt extension',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isValidExtension', ['pdf', 'png', 'txt'], false],
        'message' => 'File does not have a pdf, png, or txt extension',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isaboveminheight"></a>

## isAboveMinHeight

ファイルが必要な最小の高さを上回っていることをチェックします (ピクセル単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinHeight', 150],
        'message' => 'File is below the minimum height',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinHeight', 150, false],
        'message' => 'File is below the minimum height',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isbelowmaxheight"></a>

## isBelowMaxHeight

ファイルが必要な最大の高さを下回っていることをチェックします (ピクセル単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxHeight', 150],
        'message' => 'File is above the maximum height',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxHeight', 150, false],
        'message' => 'File is above the maximum height',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isaboveminwidth"></a>

## isAboveMinWidth

ファイルが必要な最小の幅を上回っていることをチェックします (ピクセル単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinWidth', 150],
        'message' => 'File is below the minimum width',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isAboveMinWidth', 150, false],
        'message' => 'File is below the minimum width',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。

<a id="isbelowmaxwidth"></a>

## isBelowMaxWidth

ファイルが必要な最大の幅を下回っていることをチェックします (ピクセル単位)。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxWidth', 150],
        'message' => 'File is above the maximum width',
    ],
];
```

引数 `$requireUpload` を渡すと、ファイルがアップロードされていない場合にこのチェックをスキップできます。

```php
public $validate = [
    'photo' => [
        'rule' => ['isBelowMaxWidth', 150, false],
        'message' => 'File is above the maximum width',
    ],
];
```

上の例では、変数 `$requireUpload` の値は false です。デフォルトでは `requireUpload` は true です。
