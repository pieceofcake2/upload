# Upload Documentation

The Upload plugin is an attempt to sanely upload files using techniques garnered from packages
such as [MeioUpload](http://github.com/jrbasso/MeioUpload),
[UploadPack](http://github.com/szajbus/cakephp-uploadpack) and
[PHP documentation](http://php.net/manual/en/features.file-upload.php).

pieceofcake2/upload is a fork of [josegonzalez/cakephp-upload](https://github.com/FriendsOfCake/cakephp-upload)
(2.x branch) for [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp). It replaces the
`josegonzalez/cakephp-upload` package. This documentation is based on the
[original documentation](http://cakephp-upload.readthedocs.org/en/2.x/).

- [Introduction](#introduction)
- [Installation](#installation)
- [Usage](#usage)
- [Further Documentation](#further-documentation)

## Introduction

### What does this plugin do?

- The Upload plugin will transfer files from a form in your application to (by default) the `webroot/files` directory organised by the model name and primaryKey field.
- It can also move files around programmatically. Such as from the filesystem.
- The path to which the files are saved can be customised.
- It can also create thumbnails for image files if the `thumbnailSizes` option is set in the behaviour options.
- The plugin can also upload multiple files at the same time to different fields.
- Each upload field can be configured independently of each other, such as changing the upload path or thumbnail options.
- Uploaded file information can be stored in a data store, such as a MySQL database.
- A variety of validation rules are provided to help validate against common rules.

### This plugin does not do

- It will not convert files between file types. You cannot use it to convert a JPG to a PNG.
- It will not add watermarks to images for you.

### Background

From the original author: Media Plugin is too complicated, and it was a PITA to merge the latest
updates into MeioUpload, so here I am, building yet another upload plugin. I'll build another
in a month and call it "YAUP".

## Installation

### Requirements

- PHP 8.0+
- pieceofcake2/cakephp 2.12+
- GD or Imagick PHP extension (for thumbnail creation)

### Using Composer

```bash
composer require pieceofcake2/upload
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory by
`composer/installers`. Your application's `composer.json` **must** allow the `composer/installers`
plugin and set `installer-paths`, otherwise the plugin is not installed into a directory where
`CakePlugin::load()` can find it:

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

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

It is recommended that you add `/plugins/Upload` (legacy layout: `/app/Plugin/Upload`) to your
`.gitignore` file. (Why? [read this](http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).)

### Manual

- Download this: <https://github.com/pieceofcake2/upload/archive/2.x.zip>
- Unzip that download.
- Copy the resulting folder to `plugins` (legacy layout: `app/Plugin`)
- Rename the folder you just copied to `Upload`

### Git Clone

In your `plugins` directory (legacy layout: `app/Plugin`) type:

```bash
git clone -b 2.x https://github.com/pieceofcake2/upload.git Upload
```

### Imagick Support

To enable [Imagick](http://www.imagemagick.org/) support, you need to have Imagick installed:

```bash
# Debian systems
sudo apt-get install php-imagick

# From pecl
pecl install imagick
```

If you cannot install Imagick, instead configure the plugin with `'thumbnailMethod' => 'php'`
(GD) in the field options.

### Enable plugin

Enable the plugin in your `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`) file:

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

By default files are stored in `{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}`, where `{ROOT}`
is the `rootDir` option. `rootDir` defaults to the parent directory of `WWW_ROOT`, so the files are
stored in your webroot in both the modern layout (`webroot/`) and the legacy layout (`app/webroot/`).

Use `'thumbnailMethod' => 'php'` (GD) if the Imagick extension is not installed.

## Further Documentation

- [Examples](examples.md)
- [Behavior configuration options](configuration.md)
- [Using a polymorphic attachment model for file storage](polymorphic.md)
- [Thumbnail sizes and styles](thumbnails.md)
- [Validation rules](validation.md)
- [FileImportBehavior](file-import-behavior.md)
- [Thumbnail shell](thumbnail-shell.md)
