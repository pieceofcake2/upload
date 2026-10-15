# サムネイルのサイズとスタイル

Upload プラグインは、ファイルのアップロード時に、さまざまなサイズのサムネイルを自動的に生成できます。
サムネイルを生成するには、サムネイルを設定する必要があります。

サムネイルを生成するには、設定するフィールドの下で `thumbnailSizes` オプションを設定します。

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'photo' => [ // The field we are configuring for
                'thumbnailSizes' => [ // Various sizes of thumbnail to generate
                    'big' => '200x200', // Resize for best fit to 200px by 200px, cropped from the center of the image. Prefix with big_
                    'small' => '120x120',
                    'thumb' => '80x80',
                ],
            ],
        ],
    ];
}
```

この設定をすると、ファイルのアップロード時に、オプションで定義した接頭辞の付いたサムネイルが自動的に
生成されます。たとえば (デフォルトの設定では) `webroot/files/Example/photo/1/big_example.jpg`
(レガシーレイアウトでは `app/webroot/files/...`) です。`Example` はモデル、`photo` はフィールド、`1` は
モデルの primaryKey の値で、最後の `big_` はファイル名に付くサムネイルのサイズの接頭辞です。

サムネイルのサイズは、次の種類の画像にだけ適用されます。

- image/bmp
- image/gif
- image/jpeg
- image/pjpeg
- image/png
- image/vnd.microsoft.icon
- image/x-icon

サイズには、次のリサイズモードのいずれかを指定できます。

- `100x80` - この寸法に最もよく収まるようにリサイズし、元の縦横比と異なる場合ははみ出した端を切り取ります
- `[100x80]` - この寸法に収まるようにリサイズし、元の縦横比と異なる場合は白い帯を付けます
- `100w` - 元の縦横比を保ち、幅 100 ピクセルにリサイズします
- `80h` - 元の縦横比を保ち、高さ 80 ピクセルにリサイズします
- `80l` - 元の縦横比を保ち、長い辺が 80 ピクセルになるようにリサイズします
- `600mw` - 元の縦横比を保ち、最大幅 600 ピクセルにリサイズします。元の画像の幅が 600 ピクセル未満の場合は元の画像をコピーします
- `800mh` - 元の縦横比を保ち、最大高さ 800 ピクセルにリサイズします。元の画像の高さが 800 ピクセル未満の場合は元の画像をコピーします
- `960ml` - 元の縦横比を保ち、長い辺が最大 960 ピクセルになるようにリサイズします。サムネイルが元の画像より大きくなる場合は元の画像をコピーします。

<a id="pdf-support"></a>

## PDF のサポート

PDF ファイルの最初のページのサムネイルを生成できるようになりました (`thumbnailMethod` が `imagick`
の場合のみ動作します)。[このプラグインを設定する](configuration.md) 方法の詳細については、
[ビヘイビアのオプション](configuration.md) を読んでください。
