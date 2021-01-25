<?php

namespace Yoelpc4\LaravelMedia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Yoelpc4\LaravelMedia\Support\UrlGenerator;

class Media extends Model
{
    /**
     * @var string
     */
    protected $table = 'medias';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'group',
        'name',
        'disk',
        'filename',
        'mime_type',
        'size',
    ];

    /**
     * @inheritDoc
     */
    protected $casts = [
        'size'        => 'integer',
        'conversions' => 'array',
    ];

    /**
     * @var array
     */
    protected $filterScopes = [
        'group'     => 'whereGroup',
        'name'      => 'whereName',
        'filename'  => 'whereFilename',
        'mime_type' => 'whereMimeType',
        'size'      => 'whereSize',
    ];

    /**
     * Media morph to mediable
     *
     * @return MorphTo
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Get media's extension
     *
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return UrlGenerator::make($this)->generateMediaUrl();
    }

    /**
     * Get media conversion url
     *
     * @param  string  $conversionName
     * @return string
     */
    public function getMediaConversionUrl(string $conversionName)
    {
        return UrlGenerator::make($this)->generateMediaConversionUrl($conversionName);
    }

    /**
     * Mark the media conversion as generated
     *
     * @param  string  $mediaConversionName
     * @return void
     */
    public function markAsMediaConversionGenerated(string $mediaConversionName)
    {
        $conversions = $this->conversions;

        $conversions[$mediaConversionName] = true;

        $this->conversions = $conversions;

        $this->save();
    }

    /**
     * Determine if media conversion is generated
     *
     * @param  string  $mediaConversionName
     * @return bool
     */
    public function hasGeneratedMediaConversion(string $mediaConversionName)
    {
        return array_key_exists($mediaConversionName, $this->conversions);
    }
}
