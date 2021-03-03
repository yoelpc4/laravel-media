<?php

namespace Yoelpc4\LaravelMedia;

use Illuminate\Support\Facades\Validator;
use Yoelpc4\LaravelMedia\Models\Media;
use Yoelpc4\LaravelMedia\Contracts\Mediable;
use Yoelpc4\LaravelMedia\Conversion\MediaConversionCollection;
use Yoelpc4\LaravelMedia\Conversion\MediaConverter;
use Yoelpc4\LaravelMedia\Group\MediaGroup;
use Yoelpc4\LaravelMedia\Manipulator\ImageManipulator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Symfony\Component\HttpFoundation\File\File;
use Yoelpc4\LaravelMedia\Support\Filesystem;

class MediaManager
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Mediable|Model
     */
    protected $mediable;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $filename;

    /**
     * MediaManager constructor.
     *
     * @param  Filesystem  $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Set mediable
     *
     * @param  Mediable|Model  $mediable
     * @return $this
     */
    public function setMediable($mediable)
    {
        if (! ($mediable instanceof Mediable && $mediable instanceof Model)) {
            throw new InvalidArgumentException('The mediable class '.get_class($mediable).' must be an instance of '.Mediable::class.' and '.Model::class);
        }

        $this->mediable = $mediable;

        return $this;
    }

    /**
     * Set file
     *
     * @param  string|UploadedFile  $file
     * @return $this
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            if (! is_file($file)) {
                throw new InvalidArgumentException("The file {$file} must be a valid path");
            }

            $this->path = $file;

            $this->name = pathinfo($file, PATHINFO_FILENAME);

            $this->filename = self::sanitizeFilename(pathinfo($file, PATHINFO_BASENAME));
        } elseif ($file instanceof UploadedFile) {
            $this->path = $file->getRealPath();

            $filename = $file->getClientOriginalName();

            $this->name = pathinfo($filename, PATHINFO_FILENAME);

            $this->filename = self::sanitizeFilename($filename);
        } else {
            throw new InvalidArgumentException('The file must be a path or an instance of the '.UploadedFile::class);
        }

        return $this;
    }

    /**
     * Create media based on the specified media group name
     *
     * @param  string  $mediaGroupName
     * @return Media
     * @throws ValidationException
     * @throws InvalidImageDriver
     * @throws FileNotFoundException
     */
    public function toMediaGroup(string $mediaGroupName)
    {
        try {
            $this->validate($mediaGroupName);
        } catch (ValidationException $e) {
            throw $e;
        }

        $mediaClass = config('media.model');

        $media = new $mediaClass;

        $media->group = $mediaGroupName;

        $media->name = $this->name;

        $media->disk = config('filesystems.default');

        $media->filename = $this->filename;

        $media->mime_type = mime_content_type($this->path);

        $media->size = filesize($this->path);

        $media->conversions = [];

        $this->mediable->medias()->save($media);

        $path = $this->filesystem->saveMedia($media, $this->path, $this->filename);

        try {
            if (ImageManipulator::isValid($path)) {
                ImageManipulator::make()->manipulate($path);
            }
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        try {
            $this->convertMedia($media);
        } catch (FileNotFoundException $e) {
            throw $e;
        } catch (InvalidImageDriver $e) {
            throw $e;
        }

        $this->deleteMediaFilesThatExceedsLimit($mediaGroupName);

        return $media;
    }

    /**
     * Sanitize the specified filename
     *
     * @param  string  $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename)
    {
        return str_replace(['#', '/', '\\', ' '], '-', $filename);
    }

    /**
     * Validate the given media group
     *
     * @param  string  $mediaGroupName
     * @throws ValidationException
     */
    protected function validate(string $mediaGroupName)
    {
        $this->mediable->registerMediaGroups();

        $mediaGroups = collect($this->mediable->getMediaGroups());

        $data = [
            'group' => $mediaGroupName,
        ];

        $rules = [
            'group' => [
                'required',
                'string',
                'in:'.$mediaGroups->map(function (MediaGroup $mediaGroup) {
                    return $mediaGroup->getName();
                })->implode(','),
            ],
        ];

        $messages = [];

        $attributes = [
            'group' => __('laravel-media::validation.attributes.group'),
        ];

        try {
            Validator::make($data, $rules, $messages, $attributes)->validate();
        } catch (ValidationException $e) {
            throw $e;
        }

        /** @var MediaGroup $mediaGroup */
        $mediaGroup = $mediaGroups->first(function (MediaGroup $mediaGroup) use ($mediaGroupName) {
            return $mediaGroup->getName() === $mediaGroupName;
        });

        $data = [
            'file' => new File($this->path),
        ];

        $rules = [
            'file' => ['required', 'file'],
        ];

        if ($maxFileSize = $mediaGroup->getMaxFileSize()) {
            $rules['file'][] = "max:{$mediaGroup->getMaxFileSize()}";
        }

        $mimeTypes = $mediaGroup->getMimeTypes();

        if (count($mimeTypes)) {
            $rules['file'][] = "mimetypes:".implode(',', $mimeTypes);
        }

        $messages = [];

        $attributes = [
            'file' => __('laravel-media::validation.attributes.file'),
        ];

        try {
            Validator::make($data, $rules, $messages, $attributes)->validate();
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Convert the specified media
     *
     * @param  Media  $media
     * @return void
     * @throws FileNotFoundException
     * @throws InvalidImageDriver
     */
    protected function convertMedia(Media $media)
    {
        $collection = MediaConversionCollection::makeFromMedia($media)->filterByMediaGroupName($media->group);

        if ($collection->isNotEmpty()) {
            try {
                app(MediaConverter::class)->convert($media, $collection);
            } catch (FileNotFoundException $e) {
                throw $e;
            } catch (InvalidImageDriver $e) {
                throw $e;
            }
        }
    }

    /**
     * Delete media(s) by the specified media group name, if media(s) count exceeds the media group's limit
     *
     * @param  string  $mediaGroupName
     * @return void
     */
    protected function deleteMediaFilesThatExceedsLimit(string $mediaGroupName)
    {
        $this->mediable->registerMediaGroups();

        $mediaGroup = $this->mediable->getMediaGroup($mediaGroupName);

        /** @var Collection $medias */
        $medias = $this->mediable->medias()->whereGroup($mediaGroupName)->get();

        $maxFileLimit = $mediaGroup->getMaxFileLimit();

        if (! is_null($maxFileLimit) && $medias->count() > $maxFileLimit) {
            $excludedMedias = $medias->reverse()->take($maxFileLimit);

            $medias->reject(function (Media $media) use ($excludedMedias) {
                return $excludedMedias->where('id', $media->id)->count();
            })->each->delete();
        }
    }
}
