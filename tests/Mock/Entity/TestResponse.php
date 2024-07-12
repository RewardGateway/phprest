<?php

namespace Phprest\Test\Mock\Entity;

use JMS\Serializer\Annotation as Serializer;

class TestResponse
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
