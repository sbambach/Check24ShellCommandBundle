<?php


namespace Shopping\ShellCommandBundle\Utils\Pipe\Resource;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class Stream implements ResourceInterface
{
    protected $resource;
    protected $accessType = self::ACCESS_TYPE_READ;

    /**
     * @return string
     */
    public function getAccessType(): string
    {
        return $this->accessType;
    }

    /**
     * @param string $accessType
     *
     * @return Stream
     */
    public function setAccessType(string $accessType): ResourceInterface
    {
        $this->accessType = $accessType;
        return $this;
    }

    public function openResourceHandle()
    {
        if (empty($this->resource)) {
            return [static::DESC_SPEC_PIPE, $this->accessType];
        }

        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }
}