<?php

namespace Phprest\Util;

class Version
{
    public int $major;
    public int $minor;
    public string $version;

    public function __construct(string $version)
    {
        $this->version = str_pad($version, 3, '.0');

        $this->major = (int)$this->version[0];
        $this->minor = (int)$this->version[2];
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
