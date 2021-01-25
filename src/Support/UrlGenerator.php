<?php

namespace Yoelpc4\LaravelMedia\Support;

use Illuminate\Support\Facades\Storage;
use Yoelpc4\LaravelMedia\Models\Media;

class UrlGenerator
{
    /**
     * @var Media
     */
    protected $media;

    /**
     * UrlGenerator constructor.
     *
     * @param  Media  $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Create a new url generator
     *
     * @param  Media  $media
     * @return static
     */
    public static function make(Media $media)
    {
        return new self($media);
    }

    /**
     * Generate media url
     *
     * @return string
     */
    public function generateMediaUrl()
    {
        $path = PathGenerator::make($this->media)->generateMediaFilePath();

        return $this->getUrl($path);
    }

    /**
     * Generate media conversion url
     *
     * @param  string  $mediaConversionName
     * @return string|null
     */
    public function generateMediaConversionUrl(string $mediaConversionName)
    {
        if ($path = PathGenerator::make($this->media)->generateMediaConversionFilePath($mediaConversionName)) {
            return $this->getUrl($path);
        }

        return null;
    }

    /**
     * Get url for the specified path
     *
     * @param  string  $path
     * @return string
     */
    protected function getUrl(string $path)
    {
        $filesystem = Storage::disk($this->media->disk);

        if (config('media.versioned_url')) {
            return $filesystem->url("{$path}?v={$this->media->updated_at->timestamp}");
        }

        return $filesystem->url($path);
    }
}
