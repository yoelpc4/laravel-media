<?php

namespace Yoelpc4\LaravelMedia\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yoelpc4\LaravelMedia\Concerns\InteractsWithMedia;
use Yoelpc4\LaravelMedia\Contracts\Mediable;
use Yoelpc4\LaravelMedia\Models\Media;

class User extends Model implements Mediable
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * @inheritDoc
     */
    protected $guarded = [];

    /**
     * User has one avatar image
     *
     * @return MorphOne
     */
    public function avatarImage()
    {
        return $this->morphOne(Media::class, 'mediable')->whereGroup('avatar image');
    }

    /**
     * @inheritDoc
     */
    public function registerMediaGroups()
    {
        $this->addMediaGroup('avatar image')
            ->setMimeTypes(['image/jpeg', 'image/png'])
            ->setMaxFileLimit(1);
    }
}
