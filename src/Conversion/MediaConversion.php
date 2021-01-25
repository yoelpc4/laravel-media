<?php

namespace Yoelpc4\LaravelMedia\Conversion;

use BadMethodCallException;
use Spatie\Image\Manipulations;
use Yoelpc4\LaravelMedia\MediaManager;
use Yoelpc4\LaravelMedia\Models\Media;

/* @mixin \Spatie\Image\Manipulations */
class MediaConversion
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Manipulations|callable
     */
    protected $manipulations;

    /**
     * @var array
     */
    protected $shouldBePerformedOnMediaGroups = [];

    /**
     * Media conversion constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = MediaManager::sanitizeFilename($name);

        $this->manipulations = new Manipulations;
    }

    /**
     * Create a new media conversion
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
     * Set manipulations
     *
     * @param  Manipulations|callable  $manipulations
     * @return $this
     */
    public function setManipulations($manipulations)
    {
        if ($manipulations instanceof Manipulations) {
            $this->manipulations = $this->manipulations->mergeManipulations($manipulations);
        }

        if (is_callable($manipulations)) {
            $manipulations($this->manipulations);
        }

        return $this;
    }

    /**
     * Get manipulations
     *
     * @return Manipulations|callable
     */
    public function getManipulations()
    {
        return $this->manipulations;
    }

    /**
     * Set should be performed on media groups
     *
     * @param  string  ...$mediaGroupNames
     * @return $this
     */
    public function setShouldBePerformedOnMediaGroups(...$mediaGroupNames)
    {
        $this->shouldBePerformedOnMediaGroups = $mediaGroupNames;

        return $this;
    }

    /**
     * Get should be performed on media groups
     *
     * @return array
     */
    public function getShouldBePerformedOnMediaGroups()
    {
        return $this->shouldBePerformedOnMediaGroups;
    }

    /**
     * Determine if media conversion should performed on the specified media group name
     *
     * @param  string  $mediaGroupName
     * @return bool
     */
    public function shouldPerformedOnMediaGroup(string $mediaGroupName)
    {
        return in_array($mediaGroupName, $this->shouldBePerformedOnMediaGroups);
    }

    /**
     * Get filename
     *
     * @param  Media  $media
     * @return string
     */
    public function getFilename(Media $media)
    {
        return "{$this->name}.{$media->extension}";
    }

    /**
     * Proxy inaccessible media conversion method call to the image manipulations
     *
     * @param  string  $name
     * @param  mixed  $arguments
     * @return $this
     */
    public function __call(string $name, $arguments)
    {
        if (! method_exists($this->manipulations, $name)) {
            throw new BadMethodCallException("Manipulation {$name} doesn't exist");
        }

        $this->manipulations->$name(...$arguments);

        return $this;
    }
}
