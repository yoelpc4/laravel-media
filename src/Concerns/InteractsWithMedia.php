<?php

namespace Yoelpc4\LaravelMedia\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Yoelpc4\LaravelMedia\Contracts\Mediable;
use Yoelpc4\LaravelMedia\Conversion\MediaConversion;
use Yoelpc4\LaravelMedia\Group\MediaGroup;
use Yoelpc4\LaravelMedia\MediaManager;
use Yoelpc4\LaravelMedia\Models\Media;

trait InteractsWithMedia
{
    /**
     * @var MediaGroup[]
     */
    protected $mediaGroups = [];

    /**
     * @var MediaConversion[]
     */
    protected $mediaConversions = [];

    /**
     * Boot trait on model
     *
     * @throws Exception
     */
    public static function bootInteractsWithMedia()
    {
        static::deleting(function (Mediable $mediable) {
            if (in_array(SoftDeletes::class, class_uses_recursive($mediable))) {
                /** @var SoftDeletes $mediable */
                if (! $mediable->forceDeleting) {
                    return;
                }
            }

            /** @var Model|Mediable $mediable */
            $mediable->medias()->cursor()->each(function (Media $media) {
                try {
                    $media->delete();
                } catch (Exception $e) {
                    throw $e;
                }
            });
        });
    }

    /**
     * Mediable morph many medias
     *
     * @return MorphMany
     */
    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Add file to the media manager
     *
     * @param  string|UploadedFile  $file
     * @return MediaManager
     */
    public function addFile($file)
    {
        return app(MediaManager::class)->setMediable($this)->setFile($file);
    }

    /**
     * Register media groups
     *
     * @return void
     */
    public function registerMediaGroups()
    {
        //
    }

    /**
     * Register media conversions
     *
     * @return void
     */
    public function registerMediaConversions()
    {
        $this->registerMediaGroups();

        collect($this->mediaGroups)->each(function (MediaGroup $mediaGroup) {
            $currentMediaConversions = $this->mediaConversions;

            $this->mediaConversions = [];

            ($mediaGroup->mediaConversionsRegistration)();

            $newMediaConversions = collect($this->mediaConversions)
                ->each(function (MediaConversion $mediaConversion) use ($mediaGroup) {
                    $mediaConversion->setShouldBePerformedOnMediaGroups($mediaGroup->getName());
                })
                ->values()
                ->all();

            $this->mediaConversions = Arr::collapse([$currentMediaConversions, $newMediaConversions]);
        });
    }

    /**
     * Add a media group
     *
     * @param  string  $name
     * @return MediaGroup
     */
    public function addMediaGroup(string $name)
    {
        $this->mediaGroups[] = $mediaGroup = MediaGroup::make($name);

        return $mediaGroup;
    }

    /**
     * Get the registered media groups
     *
     * @return MediaGroup[]
     */
    public function getMediaGroups()
    {
        return $this->mediaGroups;
    }

    /**
     * Get registered media group by name
     *
     * @param  string  $mediaGroupName
     * @return MediaGroup|null
     */
    public function getMediaGroup(string $mediaGroupName)
    {
        return collect($this->mediaGroups)->first(function (MediaGroup $mediaGroup) use ($mediaGroupName) {
            return $mediaGroup->getName() === $mediaGroupName;
        });
    }

    /**
     * Add a media conversion
     *
     * @param  string  $name
     * @return MediaConversion
     */
    public function addMediaConversion(string $name)
    {
        $this->mediaConversions[] = $mediaConversion = MediaConversion::make($name);

        return $mediaConversion;
    }

    /**
     * Get registered media conversions
     *
     * @return MediaConversion[]
     */
    public function getMediaConversions()
    {
        return $this->mediaConversions;
    }

    /**
     * Get the registered media conversions
     *
     * @param  string  $mediaConversionName
     * @return MediaConversion|null
     */
    public function getMediaConversion(string $mediaConversionName)
    {
        return collect($this->mediaConversions)
            ->first(function (MediaConversion $mediaConversion) use ($mediaConversionName) {
                return $mediaConversion->getName() === $mediaConversionName;
            });
    }
}
