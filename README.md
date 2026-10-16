# Upload Plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/upload?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/upload?label=Packagist)](https://packagist.org/packages/pieceofcake2/upload)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/upload/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/upload/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/upload/actions/workflows/CI.yml)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/upload/CI.yml?label=CI)](https://github.com/pieceofcake2/upload/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/upload?label=Coverage)](https://codecov.io/gh/pieceofcake2/upload)

pieceofcake2/upload is a model behavior that uploads files and generates thumbnails for
CakePHP 2 applications. It is a fork of [josegonzalez/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload)
(2.x branch) for [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp), and replaces
the `josegonzalez/cakephp-upload` package.

## Documentation

- Website: https://pieceofcake2.github.io/upload/ (日本語: https://pieceofcake2.github.io/upload/ja/)
- Markdown: [docs/README.md](docs/README.md) (日本語: [docs/ja/README.md](docs/ja/README.md)) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/Upload/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

* PHP 8.0+
* pieceofcake2/cakephp 2.12+
* GD or Imagick PHP extension (for thumbnail creation)

## Installation

```bash
composer require pieceofcake2/upload
```

Your application's `composer.json` must allow the `composer/installers` plugin and set
`installer-paths`; then load the plugin and add the `Upload.Upload` behavior to your models.
See [Installation](docs/README.md#installation) and [Usage](docs/README.md#usage).

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## Origin

This package is a maintained fork of
[FriendsOfCake/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload)
(`josegonzalez/cakephp-upload` on Packagist, last upstream version 1.3.1), originally by Jose
Diaz-Gonzalez. It is maintained for CakePHP 2 on modern PHP by
[pieceofcake2](https://github.com/pieceofcake2). Changes from the original are listed in the
[commit history since the fork](https://github.com/pieceofcake2/upload/compare/f470b9b...2.x).

## License

MIT License. See [LICENSE](LICENSE).
