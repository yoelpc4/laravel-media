<?php

namespace Yoelpc4\LaravelMedia\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yoelpc4\LaravelMedia\Concerns\InteractsWithMedia;
use Yoelpc4\LaravelMedia\Contracts\Mediable;
use Yoelpc4\LaravelMedia\Models\Media;

class Post extends Model implements Mediable
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * @inheritDoc
     */
    protected $guarded = [];

    /**
     * Post has one pdf document
     *
     * @return MorphOne
     */
    public function pdfDocument()
    {
        return $this->morphOne(Media::class, 'mediable')->whereGroup('pdf document');
    }

    /**
     * Post has many hero images
     *
     * @return MorphMany
     */
    public function heroImages()
    {
        return $this->morphMany(Media::class, 'mediable')->whereGroup('hero image');
    }

    /**
     * @inheritDoc
     */
    public function registerMediaGroups()
    {
        $this->addMediaGroup('pdf document')
            ->setMimeTypes('application/pdf')
            ->setMaxFileLimit(1);

        $this->addMediaGroup('hero image')
            ->setMimeTypes(['image/jpeg', 'image/png'])
            ->setMaxFileLimit(5)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('landscape-large')
                    ->width(1920)
                    ->height(1080);
            });
    }
}
