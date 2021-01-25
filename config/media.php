<?php

use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the media image driver for image manipulation.
    | Available options: "gd", "imagick".
    |
    */

    'image_driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Image Optimizers
    |--------------------------------------------------------------------------
    |
    | This option controls the image optimizers.
    | For more information see https://github.com/spatie/image-optimizer#optimization-tools.
    |
    */

    'image_optimizers' => [
        Jpegoptim::class => [
            '-m85', // set maximum quality to 85%
            '--strip-all', // this strips out all text information such as comments and EXIF data
            '--all-progressive', // this will make sure the resulting image is a progressive one
        ],
        Pngquant::class  => [
            '--force', // required parameter for this package
        ],
        Optipng::class   => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2', // this set the optimization level to two (multiple IDAT compression trials)
            '-quiet', // required parameter for this package
        ],
        Svgo::class      => [
            '--disable=cleanupIDs', // disabling because it is known to cause troubles
        ],
        Gifsicle::class  => [
            '-b', // required parameter for this package
            '-O3', // this produces the slowest but best results
        ],
        Cwebp::class     => [
            '-m 6', // for the slowest compression method in order to get the best compression.
            '-pass 10', // for maximizing the amount of analysis pass.
            '-mt', // multithreading for some speed improvements.
            '-q 90', //quality factor that brings the least noticeable changes.
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | This option controls the media model to create, read, and delete media record on database.
    |
    */

    'model' => \Yoelpc4\LaravelMedia\Models\Media::class,

    /*
    |--------------------------------------------------------------------------
    | Observer
    |--------------------------------------------------------------------------
    |
    | This option controls the media observer to hook on create, read, and delete media model event.
    |
    */

    'observer' => \Yoelpc4\LaravelMedia\Observers\MediaObserver::class,

    /*
    |--------------------------------------------------------------------------
    | Versioned URL
    |--------------------------------------------------------------------------
    |
    | This option controls the media URL should be versioned or not.
    |
    */

    'versioned_url' => false,

];
