<?php

namespace Phprest\Test\Mock\Controller;

use Phprest\Response\Ok;
use Phprest\Util\Controller as AbstractController;
use Phprest\Test\Mock\Entity\TestResponse;

class TestController extends AbstractController
{
    public function testAction(): Ok
    {
        return new Ok(new TestResponse('Hello Phprest World'));
    }
}
