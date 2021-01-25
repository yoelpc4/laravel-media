<?php

namespace Yoelpc4\LaravelMedia\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Validation\ValidationException;
use Spatie\Image\Exceptions\InvalidImageDriver;

class UrlTest extends TestCase
{
    /** @test */
    public function testItCanGenerateMediaUrl()
    {
        try {
            $media = $this->user->addFile($this->getFilesDirectory('image.jpg'))->toMediaGroup('avatar image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals("{$this->app['config']['filesystems.disks.public.url']}/{$media->getKey()}/{$media->filename}", $media->getMediaUrl());
    }

    /** @test */
    public function testItCanGenerateMediaConversionUrl()
    {
        $mediaConversionName = 'landscape-large';

        try {
            $media = $this->post->addFile($this->getFilesDirectory('image.jpg'))->toMediaGroup('hero image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals("{$this->app['config']['filesystems.disks.public.url']}/{$media->getKey()}/conversions/{$mediaConversionName}.jpg", $media->getMediaConversionUrl($mediaConversionName));
    }

    /** @test */
    public function testItCanGenerateVersionedMediaUrl()
    {
        $this->app['config']['media.versioned_url'] = true;

        try {
            $media = $this->user->addFile($this->getFilesDirectory('image.jpg'))->toMediaGroup('avatar image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals("{$this->app['config']['filesystems.disks.public.url']}/{$media->getKey()}/{$media->filename}?v={$media->updated_at->timestamp}", $media->getMediaUrl());
    }
}
