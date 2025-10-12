<?php

namespace Phprest\Test\Service\Hateoas;

use Phprest\Application;
use Phprest\Container;
use Phprest\Exception\NotAcceptable;
use Phprest\Exception\UnsupportedMediaType;
use Phprest\Service\Hateoas\Config;
use Phprest\Service\Hateoas\Getter;
use Phprest\Service\Hateoas\Service;
use Phprest\Service\Hateoas\Util;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Phprest\Stub\Entity\Sample;

class UtilTest extends TestCase
{
    use Getter;
    use Util;

    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();

        $service = new Service();
        $service->register($this->container, new Config(true));
    }

    public function testJsonSerialize(): void
    {
        $request = $this->setRequestParameters('phprest', '2.4', 'application/json');

        $result = $this->serialize(['a' => 1, 'b' => 2], $request, new Response());

        $this->assertEquals('{"a":1,"b":2}', $result->getContent());
    }

    public function testXmlSerialize(): void
    {
        $request = $this->setRequestParameters('phprest', '2.4', 'application/xml');

        $result = $this->serialize(['a' => 1, 'b' => 2], $request, new Response());

        $this->assertEquals(
            <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<result>
  <entry>1</entry>
  <entry>2</entry>
</result>

EOD
            ,
            $result->getContent()
        );
    }

    public function testDefaultSerialize(): void
    {
        $request = $this->setRequestParameters('phprest', '2.4', '*/*');

        $result = $this->serialize(['a' => 1, 'b' => 2], $request, new Response());

        $this->assertEquals('{"a":1,"b":2}', $result->getContent());
    }

    public function testNotAcceptableSerialize(): void
    {
        $this->expectException(NotAcceptable::class);

        $request = $this->setRequestParameters('phprest', '2.4', 'yaml');

        $this->serialize(['a' => 1, 'b' => 2], $request, new Response());
    }

    public function testJsonDeserialize(): void
    {
        $this->container->add(Application::CONTAINER_ID_VENDOR, 'phprest');
        $this->container->add(Application::CONTAINER_ID_API_VERSION, '3.2');

        $request = new Request([], [], [], [], [], [], '{"a":1,"b":2}');
        $request->headers->set('Content-Type', 'application/json');

        $sample = $this->deserialize(Sample::class, $request);

        $this->assertInstanceOf(Sample::class, $sample);
        $this->assertEquals(1, $sample->a);
        $this->assertEquals(2, $sample->b);
    }

    public function testJsonDeserializeWithUnsopportedFormat(): void
    {
        $this->expectException(UnsupportedMediaType::class);

        $this->container->add(Application::CONTAINER_ID_VENDOR, 'phprest');
        $this->container->add(Application::CONTAINER_ID_API_VERSION, '3.2');

        $request = new Request([], [], [], [], [], [], '{"a":1,"b":2}');
        $request->headers->set('Content-Type', 'application/yaml');

        $this->deserialize(Sample::class, $request);
    }

    /**
     * @param string|integer $apiVersion
     * @param string $acceptHeader
     *
     * @return Request
     */
    protected function setRequestParameters(string $vendor, $apiVersion, $acceptHeader): Request
    {
        $this->container->add(Application::CONTAINER_ID_VENDOR, $vendor);
        $this->container->add(Application::CONTAINER_ID_API_VERSION, $apiVersion);

        (new Service())->
        register($this->container, new Config(true));

        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);

        $this->container->add('Orno\Http\Request', $request);

        return $request;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @dataProvider bestMediaTypeProvider
     */
    public function testGetBestMediaType(string $acceptHeader, string $expected): void
    {
        $this->container->add(Application::CONTAINER_ID_VENDOR, 'phprest');
        $this->container->add(Application::CONTAINER_ID_API_VERSION, '2.4');

        (new Service())->register($this->container, new Config(true));

        // Call the protected method using reflection
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('getBestMediaType');
        $method->setAccessible(true);

        $result = $method->invoke($this, $acceptHeader);
        $this->assertEquals($expected, $result);
    }

    public function bestMediaTypeProvider(): array
    {
        return [
            'simple wildcard' => ['*/*', '*/*'],
            'simple json' => ['application/json', 'application/json'],
            'simple xml' => ['application/xml', 'application/xml'],
            'vendor specific json' => ['application/vnd.phprest-v2.4+json', 'application/vnd.phprest-v2.4+json'],
            'vendor specific xml' => ['application/vnd.phprest-v2.4+xml', 'application/vnd.phprest-v2.4+xml'],
            'multiple with quality - html preferred' => [
                'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8',
                'text/html'
            ],
            'multiple with quality - json preferred' => [
                'application/json;q=1.0, application/xml;q=0.5',
                'application/json'
            ],
            'multiple with quality - xml preferred' => [
                'application/json;q=0.5, application/xml;q=0.9',
                'application/xml'
            ],
            'quality with spaces' => [
                'application/json; q=0.8, application/xml; q=0.9',
                'application/xml'
            ],
            'equal quality - first wins' => [
                'application/json;q=0.9, application/xml;q=0.9',
                'application/json'
            ],
            'complex vendor with version parameter' => [
                'application/vnd.api+json;version=1',
                'application/vnd.api+json'
            ],
            'mixed priorities with wildcard' => [
                'text/html;q=0.9, application/json;q=1.0, */*;q=0.8',
                'application/json'
            ],
            'default quality of 1.0' => [
                'application/json, application/xml;q=0.5',
                'application/json'
            ],
        ];
    }
}
