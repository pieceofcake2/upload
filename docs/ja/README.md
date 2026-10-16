# Upload ドキュメント

Upload プラグインは、[MeioUpload](https://github.com/jrbasso/MeioUpload)、
[UploadPack](https://github.com/szajbus/uploadpack) などのパッケージや
[PHP のドキュメント](https://www.php.net/manual/en/features.file-upload.php) から集めた手法を使って、
ファイルをまっとうにアップロードしようという試みです。

pieceofcake2/upload は、[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) 向けの
[josegonzalez/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload) (2.x ブランチ) の
フォークです。`josegonzalez/cakephp-upload` パッケージを置き換えます。このドキュメントは
[オリジナルのドキュメント](https://cakephp-upload.readthedocs.io/en/2.x/) をもとにしています。

- [はじめに](#introduction)
- [インストール](#installation)
- [使い方](#usage)
- [その他のドキュメント](#further-documentation)

<a id="introduction"></a>

## はじめに

<a id="what-does-this-plugin-do"></a>

### このプラグインでできること

- Upload プラグインは、アプリケーションのフォームから送信されたファイルを、(デフォルトでは)
  `webroot/files` ディレクトリに、モデル名と primaryKey フィールドごとに整理して転送します。
- ファイルシステムからなど、プログラムでファイルを移動することもできます。
- ファイルを保存するパスはカスタマイズできます。
- ビヘイビアのオプションで `thumbnailSizes` オプションを設定すると、画像ファイルのサムネイルも作成できます。
- 複数のファイルを、別々のフィールドに同時にアップロードすることもできます。
- アップロードのフィールドは、アップロード先のパスやサムネイルのオプションなど、それぞれ独立して設定できます。
- アップロードしたファイルの情報は、MySQL データベースなどのデータストアに保存できます。
- よくあるルールでのバリデーションに役立つ、さまざまなバリデーションルールが用意されています。

<a id="this-plugin-does-not-do"></a>

### このプラグインでできないこと

- ファイル形式の変換はしません。JPG を PNG に変換するといった用途には使えません。
- 画像に透かしを入れることはしません。

<a id="background"></a>

### 背景

オリジナルの作者より: Media Plugin は複雑すぎるし、MeioUpload に最新の更新をマージするのは
本当に面倒だったので、こうしてまた別のアップロードプラグインを作っています。1 か月後にはまた別のものを
作って、"YAUP" と名付けるでしょう。

<a id="installation"></a>

## インストール

<a id="requirements"></a>

### 要件

- PHP 8.0+
- pieceofcake2/cakephp 2.12+
- GD または Imagick の PHP 拡張モジュール (サムネイルの作成に必要)

<a id="using-composer"></a>

### Composer を使う

```bash
composer require pieceofcake2/upload
```

プラグインのタイプは `cakephp-plugin` なので、`composer/installers` によってプラグインディレクトリに
インストールされます。アプリケーションの `composer.json` では、`composer/installers` プラグインを
許可し、`installer-paths` を設定する **必要があります**。そうしないと、`CakePlugin::load()` が
見つけられるディレクトリにプラグインがインストールされません。

```json
{
    "config": {
        "allow-plugins": {
            "composer/installers": true
        }
    },
    "extra": {
        "installer-paths": {
            "plugins/{$name}/": ["type:cakephp-plugin"]
        }
    }
}
```

| レイアウト | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

`/plugins/Upload` (レガシーレイアウトでは `/app/Plugin/Upload`) を `.gitignore` ファイルに追加することを
お勧めします。(理由は [こちら](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md)
を読んでください。)

<a id="manual"></a>

### 手動

- これをダウンロードします: <https://github.com/pieceofcake2/upload/archive/2.x.zip>
- ダウンロードしたファイルを展開します。
- できたフォルダを `plugins` (レガシーレイアウトでは `app/Plugin`) にコピーします
- コピーしたフォルダの名前を `Upload` に変更します

<a id="git-clone"></a>

### Git Clone

`plugins` ディレクトリ (レガシーレイアウトでは `app/Plugin`) で次のように入力します。

```bash
git clone -b 2.x https://github.com/pieceofcake2/upload.git Upload
```

<a id="imagick-support"></a>

### Imagick のサポート

[Imagick](https://imagemagick.org/) のサポートを有効にするには、Imagick がインストールされている
必要があります。

```bash
# Debian systems
sudo apt-get install php-imagick

# From pecl
pecl install imagick
```

Imagick をインストールできない場合は、代わりにフィールドのオプションで
`'thumbnailMethod' => 'php'` (GD) を設定してください。

<a id="enable-plugin"></a>

### プラグインの有効化

`config/bootstrap.php` (レガシーレイアウトでは `app/Config/bootstrap.php`) ファイルでプラグインを
有効にします。

```php
CakePlugin::load('Upload');
```

すでに `CakePlugin::loadAll();` を使っている場合は不要です。

<a id="usage"></a>

## 使い方

```php
class User extends AppModel
{
    public $actsAs = [
        'Upload.Upload' => [
            'photo' => [
                'thumbnailMethod' => 'php',
                'thumbnailSizes' => [
                    'thumb' => '80x80',
                ],
            ],
        ],
    ];
}
```

デフォルトでは、ファイルは `{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}` に保存されます。
`{ROOT}` は `rootDir` オプションです。`rootDir` のデフォルトは `WWW_ROOT` の親ディレクトリなので、
モダンレイアウト (`webroot/`) でもレガシーレイアウト (`app/webroot/`) でも、ファイルは webroot に
保存されます。

Imagick 拡張モジュールがインストールされていない場合は、`'thumbnailMethod' => 'php'` (GD) を
使ってください。

<a id="further-documentation"></a>

## その他のドキュメント

- [例](examples.md)
- [ビヘイビアの設定オプション](configuration.md)
- [ポリモーフィックな添付ファイルモデルを使ったファイルの保存](polymorphic.md)
- [サムネイルのサイズとスタイル](thumbnails.md)
- [バリデーションルール](validation.md)
- [FileImportBehavior](file-import-behavior.md)
- [サムネイルシェル](thumbnail-shell.md)

<a id="origin"></a>

## 由来

このパッケージは
[FriendsOfCake/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload)
(Packagist では `josegonzalez/cakephp-upload`、アップストリームの最終バージョンは 1.3.1) の
メンテナンスされたフォークで、元の作者は Jose Diaz-Gonzalez です。
[pieceofcake2](https://github.com/pieceofcake2) が、モダンな PHP で動く CakePHP 2 向けに
メンテナンスしています。オリジナルからの変更点は
[フォーク以降のコミット履歴](https://github.com/pieceofcake2/upload/compare/f470b9b...2.x)
で確認できます。
