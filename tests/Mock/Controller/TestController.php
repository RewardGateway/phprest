<?php

namespace Phprest\Test\Mock\Controller;

use Phprest\Response\Ok;
use Phprest\Util\Controller as AbstractController;

class TestController extends AbstractController
{
    public function testAction(): Ok
    {
        return new Ok(json_encode(['Hello Phprest World']));
    }
}
