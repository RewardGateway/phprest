<?php

namespace Phprest\Test\Mock\Controller;

use Phprest\Response\Ok;

class TestController
{
    public function testAction(): Ok
    {
        return new Ok(['Hello Phprest World']);
    }
}
