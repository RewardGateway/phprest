<?php

namespace Phprest\Test\Mock\Controller;

use Phprest\Util\Controller as AbstractController;
use Phprest\Test\Mock\Entity\TestResponse;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function testAction(): Response
    {
        return $this->okResponse(
            new TestResponse('Hello Phprest World')
        );
    }
}
