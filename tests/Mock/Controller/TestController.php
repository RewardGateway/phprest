<?php

namespace Phprest\Test\Mock\Controller;

use Phprest\Response\Ok;
use Phprest\Test\Mock\Entity\TestResponse;

class TestController
{
    public function testAction(): Ok
    {
        return new Ok(new TestResponse('Hello Phprest World'));
    }
}
