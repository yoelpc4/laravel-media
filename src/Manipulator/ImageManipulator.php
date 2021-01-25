<?php

namespace Yoelpc4\LaravelMedia\Manipulator;

use Spatie\Image\Exceptions\InvalidImageDriver;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Yoelpc4\LaravelMedia\Conversion\MediaConversion;

class ImageManipulator
{
    /**
     * @var MediaConversion
     */
    protected $mediaConversion;

    /**
     * Create a new image manipulator
     *
     * @return ImageManipulator
     */
    public static function make()
    {
        return new self;
    }

    /**
     * Set media conversion
     *
     * @param  MediaConversion  $mediaConversion
     * @return $this
     */
    public function setMediaConversion(MediaConversion $mediaConversion)
    {
        $this->mediaConversion = $mediaConversion;

        return $this;
    }

    /**
     * Determine if the specified path is valid image to be manipulated
     *
     * @param  string  $path
     * @return bool
     */
    public static function isValid(string $path)
    {
        return (bool) preg_match('/^image\/*/', mime_content_type($path));
    }

    /**
     * Manipulate image from the specified path
     *
     * @param  string  $sourcePath
     * @param  string|null  $resultPath
     * @return void
     * @throws InvalidImageDriver
     */
    public function manipulate(string $sourcePath, string $resultPath = null)
    {
        $manipulations = new Manipulations;

        if ($optimizers = config('media.image_optimizers')) {
            $manipulations->optimize($optimizers);
        }

        if ($this->mediaConversion) {
            $manipulations->mergeManipulations($this->mediaConversion->getManipulations());
        }

        try {
            Image::load($sourcePath)
                ->useImageDriver(config('media.image_driver'))
                ->manipulate($manipulations)
                ->save($resultPath ?? $sourcePath);
        } catch (InvalidImageDriver $e) {
            throw $e;
        }
    }
}
