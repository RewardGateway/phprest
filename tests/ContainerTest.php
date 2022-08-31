<?php

declare(strict_types=1);

namespace Phprest\Test;

use League\Container\Exception\UnresolvableDependencyException;
use Phprest\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testGettingReflectClass()
    {
        $obj = new Container();
        $obj->add(RegisteredInterface::class, new RegisteredClass());
        $obj->add(RegisteredClass::class, new RegisteredClass());

        $result = $obj->get(TestCaseClass1::class);
        $this->assertInstanceOf(TestCaseClass1::class, $result);
        $this->assertInstanceOf(RegisteredInterface::class, $result->registeredInterface);
        $this->assertInstanceOf(RegisteredClass::class, $result->registeredClass);
        $this->assertInstanceOf(UnregisteredClass::class, $result->unregisteredClass);
        $this->assertInstanceOf(EasyToResolveClass::class, $result->easyToResolveClass);
        $this->assertInstanceOf(HardToResolveClass::class, $result->hardToResolveClass);
        $this->assertNull($result->unregisteredInterfaceWithDefault);
        $this->assertInstanceOf(UnregisteredClass::class, $result->unregisteredClassWithDefault);
        $this->assertTrue($result->bool);
        $this->assertEquals(1, $result->int);
        $this->assertEquals(1.1, $result->float);
        $this->assertEquals('string', $result->string);
        $this->assertEquals([], $result->array);
        $this->assertNull($result->object);
        $this->assertNull($result->callable);
        $this->assertNull($result->iterable);
        $this->assertNull($result->resource);
        $this->assertNull($result->null);
    }

    public function testGettingReflectedClassThatCantBeResolved()
    {
        $obj = new Container();

        $this->expectException(UnresolvableDependencyException::class);
        $obj->get(TestCaseClass2::class);
    }
}

interface RegisteredInterface {}
interface UnregisteredInterface {}
class RegisteredClass implements RegisteredInterface {}
class UnregisteredClass implements UnregisteredInterface {}
class EasyToResolveClass {}
class HardToResolveClass
{
    public RegisteredInterface $test;

    public function __construct(RegisteredInterface $test)
    {
        $this->test = $test;
    }
}
class UnresolvableClass
{
    public UnregisteredInterface $test;

    public function __construct(UnregisteredInterface $test)
    {
        $this->test = $test;
    }
}

class TestCaseClass1
{
    public RegisteredInterface $registeredInterface;
    public RegisteredClass $registeredClass;
    public UnregisteredClass $unregisteredClass;
    public EasyToResolveClass $easyToResolveClass;
    public HardToResolveClass $hardToResolveClass;
    public ?UnregisteredInterface $unregisteredInterfaceWithDefault;
    public ?UnregisteredClass $unregisteredClassWithDefault;
    public bool $bool;
    public int $int;
    public float $float;
    public string $string;
    public array $array;
    public ?object $object;
    /**
     * @var callable|null
     */
    public $callable;
    public ?iterable $iterable;
    /**
     * @var null
     */
    public $resource;
    /**
     * @var null
     */
    public $null;

    public function __construct(
        RegisteredInterface $registeredInterface,
        RegisteredClass $registeredClass,
        UnregisteredClass $unregisteredClass,
        EasyToResolveClass $easyToResolveClass,
        HardToResolveClass $hardToResolveClass,
        UnregisteredInterface $unregisteredInterfaceWithDefault = null,
        UnregisteredClass $unregisteredClassWithDefault = null,
        bool $bool = true,
        int $int = 1,
        float $float = 1.1,
        string $string = 'string',
        array $array = [],
        object $object = null,
        callable $callable = null,
        iterable $iterable = null,
        $resource = null,
        $null = null
    ) {
        $this->registeredInterface = $registeredInterface;
        $this->registeredClass = $registeredClass;
        $this->unregisteredClass = $unregisteredClass;
        $this->easyToResolveClass = $easyToResolveClass;
        $this->hardToResolveClass = $hardToResolveClass;
        $this->unregisteredInterfaceWithDefault = $unregisteredInterfaceWithDefault;
        $this->unregisteredClassWithDefault = $unregisteredClassWithDefault;
        $this->bool = $bool;
        $this->int = $int;
        $this->float = $float;
        $this->string = $string;
        $this->array = $array;
        $this->object = $object;
        $this->callable = $callable;
        $this->iterable = $iterable;
        $this->resource = $resource;
        $this->null = $null;
    }
}

class TestCaseClass2
{
    public UnresolvableClass $unresolvableClass;

    public function __construct(UnresolvableClass $unresolvableClass)
    {
        $this->unresolvableClass = $unresolvableClass;
    }
}
