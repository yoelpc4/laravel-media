<?php

namespace Yoelpc4\LaravelMedia\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Yoelpc4\LaravelMedia\Conversion\MediaConversion;
use Yoelpc4\LaravelMedia\Group\MediaGroup;
use Yoelpc4\LaravelMedia\MediaManager;

interface Mediable
{
    /**
     * Mediable morph many medias
     *
     * @return MorphMany
     */
    public function medias();

    /**
     * Add file to the media manager
     *
     * @param  string|UploadedFile  $file
     * @return MediaManager
     */
    public function addFile($file);

    /**
     * Register media groups
     *
     * @return void
     */
    public function registerMediaGroups();

    /**
     * Register media conversions
     *
     * @return void
     */
    public function registerMediaConversions();

    /**
     * Add a media group
     *
     * @param  string  $name
     * @return MediaGroup
     */
    public function addMediaGroup(string $name);

    /**
     * Get the registered media groups
     *
     * @return MediaGroup[]
     */
    public function getMediaGroups();

    /**
     * Get registered media group by name
     *
     * @param  string  $mediaGroupName
     * @return MediaGroup|null
     */
    public function getMediaGroup(string $mediaGroupName);

    /**
     * Add a media conversion
     *
     * @param  string  $name
     * @return MediaConversion
     */
    public function addMediaConversion(string $name);

    /**
     * Get registered media conversions
     *
     * @return MediaConversion[]
     */
    public function getMediaConversions();

    /**
     * Get registered media conversion by name
     *
     * @param  string  $mediaConversionName
     * @return MediaConversion|null
     */
    public function getMediaConversion(string $mediaConversionName);
}
