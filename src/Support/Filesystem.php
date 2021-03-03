<?php

namespace Yoelpc4\LaravelMedia\Support;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Yoelpc4\LaravelMedia\Models\Media;

class Filesystem
{
    /**
     * @var Factory
     */
    protected $filesystem;

    /**
     * Filesystem constructor.
     *
     * @param  Factory  $filesystem
     */
    public function __construct(Factory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Save media file to the disk
     *
     * @param  Media  $media
     * @param  string  $fromPath
     * @param  string  $filename
     * @return string
     */
    public function saveMedia(Media $media, string $fromPath, string $filename)
    {
        $directory = $this->getMediaDirectory($media);

        $stream = fopen($fromPath, 'r');

        $disk = $this->filesystem->disk($media->disk);

        $toPath = "{$directory}/{$filename}";

        $disk->put($toPath, $stream);

        fclose($stream);

        return $disk->path($toPath);
    }

    /**
     * Save media conversion file to the disk
     *
     * @param  Media  $media
     * @param  string  $path
     * @param  string  $filename
     * @return void
     */
    public function saveMediaConversion(Media $media, string $path, string $filename)
    {
        $directory = $this->getMediaConversionDirectory($media);

        $stream = fopen($path, 'r');

        $this->filesystem->disk($media->disk)->put("{$directory}/{$filename}", $stream);

        fclose($stream);
    }

    /**
     * Copy file from media library to the specified path
     *
     * @param  Media  $media
     * @param  string  $toPath
     * @return bool
     * @throws FileNotFoundException
     */
    public function copy(Media $media, string $toPath)
    {
        touch($toPath);

        $directory = $this->getMediaDirectory($media);

        $filename = $media->filename;

        try {
            $baseStream = $this->filesystem->disk($media->disk)->readStream("{$directory}/{$filename}");
        } catch (FileNotFoundException $e) {
            throw $e;
        }

        $targetStream = fopen($toPath, 'a');

        while (! feof($baseStream)) {
            $chunk = fgets($baseStream, 1024);

            fwrite($targetStream, $chunk);
        }

        fclose($baseStream);

        fclose($targetStream);

        return $toPath;
    }

    /**
     * Get media directory
     *
     * @param  Media  $media
     * @return string
     */
    protected function getMediaDirectory(Media $media)
    {
        $filesystem = $this->filesystem->disk($media->disk);

        $path = PathGenerator::make($media)->generateMediaDirectoryPath();

        if (! $filesystem->exists($path)) {
            $filesystem->makeDirectory($path);
        }

        return $path;
    }

    /**
     * Get media conversion directory
     *
     * @param  Media  $media
     * @return string
     */
    protected function getMediaConversionDirectory(Media $media)
    {
        $filesystem = $this->filesystem->disk($media->disk);

        $path = PathGenerator::make($media)->generateMediaConversionDirectoryPath();

        if (! $filesystem->exists($path)) {
            $filesystem->makeDirectory($path);
        }

        return $path;
    }
}
