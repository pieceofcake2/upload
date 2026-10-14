# Upload Plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/upload?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/upload?label=Packagist)](https://packagist.org/packages/pieceofcake2/upload)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/upload/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/upload/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/upload/CI.yml?label=CI)](https://github.com/pieceofcake2/upload/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/upload?label=Coverage)](https://codecov.io/gh/pieceofcake2/upload)

__This is forked for CakePHP2.__

The Upload Plugin is an attempt to sanely upload files using techniques garnered from packages such as [MeioUpload](http://github.com/jrbasso/MeioUpload) , [UploadPack](http://github.com/szajbus/cakephp-uploadpack) and [PHP documentation](http://php.net/manual/en/features.file-upload.php).

This is a fork of [josegonzalez/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload) (2.x branch) for [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).
It replaces the `josegonzalez/cakephp-upload` package.

## Requirements

* CakePHP 2.12+
* PHP 8.0+
* GD or Imagick PHP extension (for thumbnail creation)

## Installation

```bash
composer require pieceofcake2/upload
```

CakePHP 2 plugins are installed by [composer/installers](https://github.com/composer/installers).
Your application's `composer.json` **must** allow the `composer/installers` plugin and set `installer-paths`,
otherwise the plugin is not installed into a directory where `CakePlugin::load()` can find it:

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

For the classic `app/` layout use `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` instead.

### Enable plugin

Enable the plugin in your `config/bootstrap.php` (or `app/Config/bootstrap.php`) file:

```php
CakePlugin::load('Upload');
```

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

## Usage

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

By default files are stored in `{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}`, where `{ROOT}` is the
`rootDir` option. `rootDir` defaults to the parent directory of `WWW_ROOT`, so the files are stored in your
webroot in both the classic `app/` layout and the modern `src/` + `webroot/` layout.

Use `'thumbnailMethod' => 'php'` (GD) if the Imagick extension is not installed.

## Documentation

For documentation, please see the [docs](docs/) directory of this repository
(the original documentation is available at [cakephp-upload.readthedocs.org](http://cakephp-upload.readthedocs.org/en/2.x/)).

## License

See [LICENSE](LICENSE) for details.
