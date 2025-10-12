<?php

namespace Phprest\Test\Middleware;

use Mockery;
use Mockery\MockInterface;
use Phprest\Application;
use Phprest\Config;
use Phprest\Middleware\ApiVersion;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiVersionTest extends TestCase
{
    /**
     * @dataProvider appProvider
     *
     * @param Application $app
     */
    public function testHandle(Application $app): void
    {
        $middleware = new ApiVersion($app);

        /** @var MockInterface $app */
        $app->shouldReceive('handle')->andReturnUsing(function ($request) {
            $this->assertInstanceOf(\Phprest\HttpFoundation\Request::class, $request);

            /** @var \Phprest\HttpFoundation\Request $request */
            $this->assertEquals('/2.6/temperatures', $request->getPathInfo());
        });

        $middleware->handle(
            Request::create('/temperatures')
        );
    }

    public function appProvider(): array
    {
        $app = Mockery::mock(Application::class);

        $config = new Config('test', '2.6');
        $config->getContainer()->add(Application::CONTAINER_ID_VENDOR, $config->getVendor());
        $config->getContainer()->add(Application::CONTAINER_ID_API_VERSION, $config->getApiVersion());
        $config->getContainer()->add(Application::CONTAINER_ID_DEBUG, $config->isDebug());

        $app->shouldReceive('getConfiguration')->andReturn($config);
        $app->shouldReceive('getContainer')->andReturn($config->getContainer());

        return [[$app]];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider bestMediaTypeProvider
     */
    public function testGetBestMediaType(string $acceptHeader, string $expected): void
    {
        $app = Mockery::mock(Application::class);
        $config = new Config('test', '2.6');
        $config->getContainer()->add(Application::CONTAINER_ID_VENDOR, $config->getVendor());
        $config->getContainer()->add(Application::CONTAINER_ID_API_VERSION, $config->getApiVersion());
        $config->getContainer()->add(Application::CONTAINER_ID_DEBUG, $config->isDebug());
        
        $app->shouldReceive('getConfiguration')->andReturn($config);
        $app->shouldReceive('getContainer')->andReturn($config->getContainer());

        $middleware = new class($app) extends ApiVersion {
            public function exposedGetBestMediaType(string $acceptHeader): string
            {
                return $this->getBestMediaType($acceptHeader);
            }
        };

        $result = $middleware->exposedGetBestMediaType($acceptHeader);
        $this->assertEquals($expected, $result);
    }

    public function bestMediaTypeProvider(): array
    {
        return [
            'simple wildcard' => ['*/*', '*/*'],
            'simple json' => ['application/json', 'application/json'],
            'simple xml' => ['application/xml', 'application/xml'],
            'vendor specific' => ['application/vnd.phprest-v1.0+json', 'application/vnd.phprest-v1.0+json'],
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
            'complex vendor with parameters' => [
                'application/vnd.api+json;version=1',
                'application/vnd.api+json'
            ],
            'mixed priorities' => [
                'text/html;q=0.9, application/json;q=1.0, */*;q=0.8',
                'application/json'
            ],
        ];
    }
}
