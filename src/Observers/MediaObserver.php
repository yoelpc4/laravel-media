<?php

namespace Yoelpc4\LaravelMedia\Observers;

use Illuminate\Support\Facades\Storage;
use Yoelpc4\LaravelMedia\Models\Media;
use Yoelpc4\LaravelMedia\Support\PathGenerator;

class MediaObserver
{
    /**
     * Handle the media "deleted" event.
     *
     * @param  Media  $media
     * @return void
     */
    public function deleted(Media $media)
    {
        $path = PathGenerator::make($media)->generateMediaDirectoryPath();

        Storage::disk($media->disk)->deleteDirectory($path);
    }
}
