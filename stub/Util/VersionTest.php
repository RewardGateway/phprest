<?php

namespace Phprest\Stub\Util;

use Phprest\Util\Version;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testVersionParse()
    {
        $version = new Version('2.3');
        $this->assertSame(2, $version->major);
        $this->assertSame(3, $version->minor);
        $this->assertSame('2.3', $version->version);

        $version = new Version('3');
        $this->assertSame(3, $version->major);
        $this->assertSame(0, $version->minor);
        $this->assertSame('3.0', $version->version);
    }
}
