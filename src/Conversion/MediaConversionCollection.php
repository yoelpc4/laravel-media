<?php

namespace Yoelpc4\LaravelMedia\Conversion;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Yoelpc4\LaravelMedia\Contracts\Mediable;
use Yoelpc4\LaravelMedia\Models\Media;

class MediaConversionCollection extends Collection
{
    /**
     * @var Media
     */
    protected $media;

    /**
     * Create a new conversion collection from the specified media
     *
     * @param  Media  $media
     * @return self
     */
    public static function makeFromMedia(Media $media)
    {
        return (new self)->init($media);
    }

    /**
     * Initialize media conversion collection from the specified media
     *
     * @param  Media  $media
     * @return $this
     */
    public function init(Media $media)
    {
        $this->media = $media;

        $this->items = [];

        $mediableClass = Arr::get(Relation::morphMap(), $media->mediable_type, $media->mediable_type);

        if (is_null($mediableClass)) {
            throw new InvalidArgumentException("The mediable class {$mediableClass} isn't found");
        }

        $mediable = new $mediableClass;

        if (! $mediable instanceof Mediable) {
            throw new InvalidArgumentException("The instance of {$mediableClass} must implements ".Mediable::class);
        }

        $mediable->registerMediaConversions();

        $this->items = $mediable->getMediaConversions();

        return $this;
    }

    /**
     * First media conversions by name
     *
     * @param  string  $name
     * @return MediaConversion|null
     */
    public function firstByName(string $name)
    {
        return $this->first(function (MediaConversion $mediaConversion) use ($name) {
            return $mediaConversion->getName() === $name;
        });
    }

    /**
     * Filter media conversions by media group name
     *
     * @param  string  $mediaGroupName
     * @return MediaConversionCollection
     */
    public function filterByMediaGroupName(string $mediaGroupName)
    {
        return $this->filter(function (MediaConversion $mediaConversion) use ($mediaGroupName) {
            return $mediaConversion->shouldPerformedOnMediaGroup($mediaGroupName);
        });
    }
}
