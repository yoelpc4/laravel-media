# Laravel Media

[![Packagist][ico-packagist]][link-packagist]
[![Downloads][ico-downloads]][link-packagist]
[![Build][ico-build]][link-build]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Software License][ico-license]](LICENSE.md)
[![Contributor Covenant][ico-code-of-conduct]](CODE_OF_CONDUCT.md)

_Laravel Media manager._

## Requirement

- [Laravel](https://laravel.com)

## Install

Require this package with composer via command:

```shell
composer require yoelpc4/laravel-media
```

## Package Publication

Publish package configuration via command:

```shell
php artisan vendor:publish --provider="\Yoelpc4\LaravelMedia\MediaServiceProvider" --tag=config
```

Publish package migrations via command:

```shell
php artisan vendor:publish --provider="\Yoelpc4\LaravelMedia\MediaServiceProvider" --tag=migrations
```

Publish package resources via command:

```shell
php artisan vendor:publish --provider="\Yoelpc4\LaravelMedia\MediaServiceProvider" --tag=resources
```

## Configure

### Image Optimizers

By default, all images added to the media will be optimized according to `image_optimizers` on the media config. For
more information visit https://github.com/spatie/image-optimizer#optimization-tools.

### Media Model

Customize media model by extending `\Yoelpc4\LaravelMedia\Models\Media`, then register your model on `model` option.

### Media Observer

Customize media observer by extending `\Yoelpc4\LaravelMedia\Observers\MediaObserver`, then register your observer
on `observer` option.

### Versioned URL

Generate media URL with version on query params i.e: http://localhost/storage/1/image.jpg?v=1610970549 by setting `versioned_url` option to true.

## Mediable Model

### Mediable Contract and InteractsWithMedia Concern

The model that has media must implement `\Yoelpc4\LaravelMedia\Contracts\Mediable` interface and
use `\Yoelpc4\LaravelMedia\Concerns\InteractsWithMedia` trait.

Example Mediable Contract and InteractsWithMedia Concern usage with profileImage relationship on User model

```php
class User extends \Illuminate\Database\Eloquent\Model implements \Yoelpc4\LaravelMedia\Contracts\Mediable {
    use \Yoelpc4\LaravelMedia\Concerns\InteractsWithMedia;
    
    /**
     * User morph one profile image
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function profileImage()
    {
        return $this->morphOne(\Yoelpc4\LaravelMedia\Models\Media::class, 'mediable')->whereGroup('profile image');
    }
}
```

### Media Group Registration

Example register the media group on your mediable model on Project model

```php
$this->addMediaGroup('contract document')
    ->setMimeTypes('application/pdf')
    ->setMaxFileLimit(3);
```

### Media Conversion Registration

Example register the media conversion on media group registration on User model

```php
$this->addMediaGroup('profile image')
    ->setMimeTypes(['image/jpeg', 'image/png'])
    ->setMaxFileLimit(1)
    ->registerMediaConversions(function () {
        $this->addMediaConversion('square-thumb')
            ->width(160)
            ->height(160);
    });
```

## Media Group

The media group has name, mime types, max file size, max file limit & media conversions registration properties. 
It consists of one file or many files. The mime types, max file size, max file limit properties will be used on
validation when adding file to the media. The default media group has empty array mime types, 2MB max file size, & null
max file limit.

If media group has max file limit with integer data type (other than null), this package will keep last of max file
limit file(s) on disk and remove the other file(s). For example User model has profile image media group with max file
limit 1, and we have an user id 1 has a profile image as media id 1 on database. If we try to upload a profile image on
user id 1, then media id 1 and old profile image will be deleted and user id 1 ends up with media id 2 and new profile
image.

## Media Conversion

The media conversion has name, manipulations, and should be performed on media group properties. 
It is intended to the media with file type image. 
You can call `\Spatie\Image\Manipulations` methods on media conversion registration because it has `__call` magic method, the proxied method call will be registered as it's manipulations.

## Add Media File to Mediable

### Add Media File from Path

Example add a media file from storage path to the first User model on database

```php
try {
$path = storage_path('avatar.jpg');

$media = User::first()->addFile()->toMediaGroup('profile image');
} catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
    throw $e;
} catch (\Illuminate\Validation\ValidationException $e) {
    throw $e;
} catch (\Spatie\Image\Exceptions\InvalidImageDriver $e) {
    throw $e;
}
```

### Add Media File from Uploaded File

Example add a media file from `\Illuminate\Http\UploadedFile` to the unsaved Project model

```php
try {
$uploadedFile = request()->file('file');

$media = (new Project)->addFile($uploadedFile)->toMediaGroup('contract document');
} catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
    throw $e;
} catch (\Illuminate\Validation\ValidationException $e) {
    throw $e;
} catch (\Spatie\Image\Exceptions\InvalidImageDriver $e) {
    throw $e;
}
```

## Media URL Generation

### Get Media URL

Example get media url on media

```php
$url = Media::first()->getMediaUrl();
```

### Get Media Conversion URL

Example get media conversion url on related media

```php
$url = User::first()->profileImage->getMediaConversionUrl('square-thumb');
```

If the media doesn't have the specified media conversion name it'll return null.

## Difference From [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)

### Media Group vs Media Collection

laravel-media use media group as the term of similar media type, while laravel-medialibrary use media collection as the term.

### Mandatory Media Group

laravel-media require you to add file to the named media group on mediable model.
While laravel-medialibrary offers flexibility to omit media group name and gives you "default" as media group name.

### Validation Exception

laravel-media always perform validation based on registered media group's mime types and max file size, the `\Illuminate\Validation\ValidationException` will be thrown when validation is unmet. 
On the other hand laravel-medialibrary throws `\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection` instead of `\Illuminate\Validation\ValidationException`.

### Add File to Unsaved Mediable Model

laravel-media allows you to add media file to the unsaved mediable model.
While laravel-medialibrary expects every media must be attached to the saved mediable model.

For more information see issues here:

- https://github.com/spatie/laravel-medialibrary/issues/343
- https://github.com/spatie/laravel-medialibrary/issues/1060
- https://github.com/spatie/laravel-medialibrary/issues/1384
- https://github.com/spatie/laravel-medialibrary/issues/1423
- https://github.com/spatie/laravel-medialibrary/issues/1444

Also the pull request here:

- https://github.com/spatie/laravel-medialibrary/pull/1443

### File source

laravel-media only allows you to add file from disk path and uploaded file.
While laravel-media allows you to add file from disk path, remote file, uploaded file, symfony file, temporary upload, url, & request.

### Comprehensive vs Narrow Solution

laravel-media offers you narrow solution with limited features for specific requirements (validation exception, unsaved mediable model, & simple file source).
While laravel-medialibrary offers you comprehensive solution with rich features and battle tested.
So consider carefully prior to install this package.

## License

The Laravel Media is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

[ico-packagist]: https://img.shields.io/packagist/v/yoelpc4/laravel-media.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/yoelpc4/laravel-media.svg?style=flat-square

[ico-build]: https://travis-ci.com/yoelpc4/laravel-media.svg?branch=master&style=flat-square

[ico-code-coverage]: https://codecov.io/gh/yoelpc4/laravel-media/branch/master/graph/badge.svg?style=flat-square

[ico-license]: https://img.shields.io/packagist/l/yoelpc4/laravel-media.svg?style=flat-square

[ico-code-of-conduct]: https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg

[link-packagist]: https://packagist.org/packages/yoelpc4/laravel-media

[link-build]: https://travis-ci.com/yoelpc4/laravel-media

[link-code-coverage]: https://codecov.io/gh/yoelpc4/laravel-media
