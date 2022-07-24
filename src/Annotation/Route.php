<?php

namespace Phprest\Annotation;

use InvalidArgumentException;
use LogicException;
use Phprest\Application;
use Phprest\Util\Version;

/**
 * @Annotation
 *
 * @Target("METHOD")
 */
class Route
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $path;

    /**
     * @var null|string
     */
    public ?string $version;

    /**
     * @param mixed $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($options)
    {
        $this->validate($options);

        $version = null;

        if (isset($options['since'], $options['until'])) {
            $version = $this->getSinceUntilRegExp($options['since'], $options['until']);
        } elseif (isset($options['since'])) {
            $version = $this->getSinceRegExp($options['since']);
        } elseif (isset($options['until'])) {
            $version = $this->getUntilRegExp($options['until']);
        }

        $this->method   = $options['method'];
        $this->path     = $options['path'];
        $this->version  = null !== $version ? '{version:' . $version . '}' : '{version:any}';
    }

    /**
     * @param array $options
     *
     * @return void
     */
    protected function validate(array $options): void
    {
        if (! isset($options['method'])) {
            throw new InvalidArgumentException('method property is missing');
        } elseif (! in_array($options['method'], ['GET', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'DELETE', 'HEAD'])) {
            throw new InvalidArgumentException('method property is not valid');
        } elseif (! isset($options['path'])) {
            throw new InvalidArgumentException('path property is missing');
        } elseif (
            isset($options['since'])
            && ! preg_match('#^' . Application::API_VERSION_REG_EXP . '$#', $options['since'])
        ) {
            throw new InvalidArgumentException('since property is not valid');
        } elseif (
            isset($options['until'])
            && ! preg_match('#^' . Application::API_VERSION_REG_EXP . '$#', $options['until'])
        ) {
            throw new InvalidArgumentException('until property is not valid');
        }
    }

    /**
     * @param string $sinceVersion
     * @param string $untilVersion
     *
     * @return string
     */
    protected function getSinceUntilRegExp($sinceVersion, $untilVersion)
    {
        $sinceVersion = new Version($sinceVersion);
        $untilVersion = new Version($untilVersion);

        if (version_compare($sinceVersion->version, $untilVersion->version, 'gt')) {
            throw new LogicException('since must be lesser than until');
        }

        if ($sinceVersion->major === $untilVersion->major) {
            return sprintf(
                '(?:%d\.[%d-%d])',
                $sinceVersion->major,
                $sinceVersion->minor,
                $untilVersion->minor
            );
        } elseif (abs($sinceVersion->major - $untilVersion->major) === 1) {
            return sprintf(
                '(?:%d\.[%d-9])|(?:%d\.[0-%d])',
                $sinceVersion->major,
                $sinceVersion->minor,
                $untilVersion->major,
                $untilVersion->minor
            );
        } else {
            return sprintf(
                '(?:%d\.[%d-9])|(?:%d\.[0-%d])|(?:[%d-%d]\.\d)',
                $sinceVersion->major,
                $sinceVersion->minor,
                $untilVersion->major,
                $untilVersion->minor,
                min(9, $sinceVersion->major + 1),
                max(0, $untilVersion->major - 1)
            );
        }
    }

    /**
     * @param string $versionString
     *
     * @return string
     */
    protected function getSinceRegExp($versionString): string
    {
        $version = new Version($versionString);

        return sprintf(
            '(?:[%d-9]\.[%d-9])|(?:[%d-9]\.\d)',
            $version->major,
            $version->minor,
            min(9, $version->major + 1)
        );
    }

    /**
     * @param string $versionString
     *
     * @return string
     */
    protected function getUntilRegExp($versionString): string
    {
        $version = new Version($versionString);

        return sprintf(
            '(?:[0-%d]\.[0-%d])|(?:[0-%d]\.\d)',
            $version->major,
            $version->minor,
            max(0, $version->major - 1)
        );
    }
}
