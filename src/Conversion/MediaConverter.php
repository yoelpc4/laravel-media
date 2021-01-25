<?php

namespace Yoelpc4\LaravelMedia\Conversion;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Yoelpc4\LaravelMedia\Support\Filesystem;
use Yoelpc4\LaravelMedia\Manipulator\ImageManipulator;
use Yoelpc4\LaravelMedia\Models\Media;

class MediaConverter
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImageManipulator
     */
    protected $mediaManipulator;

    /**
     * @var TemporaryDirectory
     */
    protected $temporaryDirectory;

    /**
     * @var Media
     */
    protected $media;

    /**
     * @var MediaConversionCollection
     */
    protected $collection;

    /**
     * MediaConverter constructor.
     *
     * @param  Filesystem  $filesystem
     * @param  ImageManipulator  $mediaManipulator
     */
    public function __construct(Filesystem $filesystem, ImageManipulator $mediaManipulator)
    {
        $this->filesystem = $filesystem;

        $this->mediaManipulator = $mediaManipulator;

        $this->temporaryDirectory = (new TemporaryDirectory)->create();
    }

    /**
     * Convert the specified media
     *
     * @param  Media  $media
     * @param  MediaConversionCollection  $collection
     * @return void
     * @throws FileNotFoundException
     * @throws InvalidImageDriver
     */
    public function convert(Media $media, MediaConversionCollection $collection)
    {
        $this->media = $media;

        $copiedMediaPath = $this->createTemporaryPath();

        try {
            $this->filesystem->copy($this->media, $copiedMediaPath);
        } catch (FileNotFoundException $e) {
            throw $e;
        }

        if (ImageManipulator::isValid($copiedMediaPath)) {
            $collection->each(function (MediaConversion $mediaConversion) use ($copiedMediaPath) {
                $convertedMediaPath = $this->createTemporaryPath();

                $this->mediaManipulator
                    ->setMediaConversion($mediaConversion)
                    ->manipulate($copiedMediaPath, $convertedMediaPath);

                $this->filesystem->saveMediaConversion(
                    $this->media,
                    $convertedMediaPath,
                    $mediaConversion->getFilename($this->media)
                );

                $this->media->markAsMediaConversionGenerated($mediaConversion->getName());
            });
        }

        $this->temporaryDirectory->delete();
    }

    /**
     * Create temporary path on temporary directory
     *
     * @return string
     */
    protected function createTemporaryPath()
    {
        return $this->temporaryDirectory->path(Str::random(32).".{$this->media->extension}");
    }
}
