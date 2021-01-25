<?php

namespace Yoelpc4\LaravelMedia\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Yoelpc4\LaravelMedia\Support\PathGenerator;

class AddFileTest extends TestCase
{
    /** @test */
    public function testItCanAddImageFromPath()
    {
        $path = $this->getFilesDirectory('image.jpg');

        try {
            $media = $this->user->addFile($path)->toMediaGroup('avatar image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals('image', $media->name);

        $this->assertEquals('image.jpg', $media->filename);

        $this->assertEquals('image/jpeg', $media->mime_type);

        Storage::disk($media->disk)->assertExists(PathGenerator::make($media)->generateMediaFilePath());
    }

    /** @test */
    public function testItCanAddImageFromUploadedFile()
    {
        $uploadedFile = UploadedFile::fake()->image('fake.jpg', 1920, 1080);

        try {
            $media = $this->user->addFile($uploadedFile)->toMediaGroup('avatar image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals('fake', $media->name);

        $this->assertEquals('fake.jpg', $media->filename);

        $this->assertEquals('image/jpeg', $media->mime_type);

        Storage::disk($media->disk)->assertExists(PathGenerator::make($media)->generateMediaFilePath());
    }

    /** @test */
    public function testItCanAddDocumentFromPath()
    {
        $path = $this->getFilesDirectory('document.pdf');

        try {
            $media = $this->post->addFile($path)->toMediaGroup('pdf document');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertEquals('document', $media->name);

        $this->assertEquals('document.pdf', $media->filename);

        $this->assertEquals('application/pdf', $media->mime_type);

        Storage::disk($media->disk)->assertExists(PathGenerator::make($media)->generateMediaFilePath());
    }

    /** @test */
    public function testItCanAddImageWithConversions()
    {
        $path = $this->getFilesDirectory('image.jpg');

        try {
            $media = $this->post->addFile($path)->toMediaGroup('hero image');
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->assertArrayHasKey('landscape-large', $media->conversions);

        Storage::disk($media->disk)->assertExists(PathGenerator::make($media)->generateMediaConversionFilePath('landscape-large'));
    }
}
