<?php

namespace Yoelpc4\LaravelMedia\Support;

use Yoelpc4\LaravelMedia\Conversion\MediaConversion;
use Yoelpc4\LaravelMedia\Conversion\MediaConversionCollection;
use Yoelpc4\LaravelMedia\Models\Media;

class PathGenerator
{
    /**
     * @var Media
     */
    protected $media;

    /**
     * PathGenerator constructor.
     *
     * @param  Media  $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Create a new path generator
     *
     * @param  Media  $media
     * @return PathGenerator
     */
    public static function make(Media $media)
    {
        return new self($media);
    }

    /**
     * Generate media directory path
     *
     * @return string
     */
    public function generateMediaDirectoryPath()
    {
        return $this->media->getKey();
    }

    /**
     * Generate media file path
     *
     * @return string
     */
    public function generateMediaFilePath()
    {
        $directory = $this->generateMediaDirectoryPath();

        $filename = $this->media->filename;

        return "{$directory}/{$filename}";
    }

    /**
     * Generate media conversion directory path
     *
     * @return string
     */
    public function generateMediaConversionDirectoryPath()
    {
        return "{$this->generateMediaDirectoryPath()}/conversions";
    }

    /**
     * Generate media conversion file path
     *
     * @param  string  $mediaConversionName
     * @return string|null
     */
    public function generateMediaConversionFilePath(string $mediaConversionName)
    {
        if ($this->media->hasGeneratedMediaConversion($mediaConversionName)) {
            $directory = $this->generateMediaConversionDirectoryPath();

            /** @var MediaConversion $mediaConversion */
            $mediaConversion = MediaConversionCollection::makeFromMedia($this->media)->firstByName($mediaConversionName);

            $filename = $mediaConversion->getFilename($this->media);

            return "{$directory}/{$filename}";
        }

        return null;
    }
}
