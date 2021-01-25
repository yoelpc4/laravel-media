<?php

namespace Yoelpc4\LaravelMedia\Group;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class MediaGroup
{
    /**
     * @var callable
     */
    public $mediaConversionsRegistration;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $mimeTypes = [];

    /**
     * @var int
     */
    protected $maxFileSize = 2048;

    /**
     * @var int|null
     */
    protected $maxFileLimit;

    /**
     * MediaGroup constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $this->mediaConversionsRegistration = function () {
        };
    }

    /**
     * Create a new media group
     *
     * @param  string  $name
     * @return self
     */
    public static function make(string $name)
    {
        return new self($name);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mime types
     *
     * @param  string|array  $mimeTypes
     * @return $this
     */
    public function setMimeTypes($mimeTypes)
    {
        $mimeTypes = Arr::wrap($mimeTypes);

        if (empty($mimeTypes)) {
            throw new InvalidArgumentException('Media group at least has a mime types');
        }

        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    /**
     * Get mime types
     *
     * @return array
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    /**
     * Set max file size in kilobytes
     *
     * @param  int  $maxFileSize
     * @return $this
     */
    public function setMaxFileSize(int $maxFileSize)
    {
        $runtimeUploadMaxFilesize = str_replace('M', '', ini_get('upload_max_filesize')) * 1024;

        if ($maxFileSize < 1 && $maxFileSize > $runtimeUploadMaxFilesize) {
            throw new InvalidArgumentException("Media group max file size must between 1-{$runtimeUploadMaxFilesize} kilobyte");
        }

        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get max file size
     *
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * Set max file limit
     *
     * @param  int  $maxFileLimit
     * @return $this
     */
    public function setMaxFileLimit(int $maxFileLimit)
    {
        if ($maxFileLimit < 1) {
            throw new InvalidArgumentException('Media group at least has 1 max file limit');
        }

        $this->maxFileLimit = $maxFileLimit;

        return $this;
    }

    /**
     * Get max file limit
     *
     * @return int|null
     */
    public function getMaxFileLimit()
    {
        return $this->maxFileLimit;
    }

    /**
     * Register media group media conversions registration
     *
     * @param  callable  $mediaConversionsRegistration
     * @return void
     */
    public function registerMediaConversions(callable $mediaConversionsRegistration)
    {
        $this->mediaConversionsRegistration = $mediaConversionsRegistration;
    }
}
