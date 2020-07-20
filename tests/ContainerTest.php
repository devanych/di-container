<?php

declare(strict_types=1);

namespace Devanych\Tests\Di;

use Closure;
use Devanych\Di\Container;
use Devanych\Tests\Di\TestAsset\AutoWiringDummyFactory;
use Devanych\Tests\Di\TestAsset\DummyFactory;
use Devanych\Tests\Di\TestAsset\DummyData;
use Devanych\Tests\Di\TestAsset\DummyName;
use Devanych\Tests\Di\TestAsset\AutoWiring;
use Devanych\Di\Exception\NotFoundException;
use Devanych\Di\Exception\ContainerException;
use Devanych\Tests\Di\TestAsset\AutoWiringScalarNotDefault;
use PHPUnit\Framework\TestCase;
use StdClass;

use function microtime;

class ContainerTest extends TestCase
{
    public function testSetAndGetScalarDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'integer', $definition = 5);
        $this->assertSame($definition, $container->get($id));

        $container->set($id = 'float', $definition = 3.7);
        $this->assertSame($definition, $container->get($id));

        $container->set($id = 'boolean', $definition = false);
        $this->assertSame($definition, $container->get($id));

        $container->set($id = 'string', $definition = 'string');
        $this->assertSame($definition, $container->get($id));
    }

    public function testSetAndGetArrayDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'empty', $definition = []);
        $this->assertSame($definition, $container->get($id));

        $container->set($id = 'array', $definition = ['array']);
        $this->assertSame($definition, $container->get($id));

        $container->set($id = 'nested', $definition = [
            'nested' => [
                'scalar' => [
                    'integer' => 5,
                    'float' => 3.7,
                    'boolean' => false,
                    'string' => 'string',
                ],
                'not_scalar' => [
                    'object' => new StdClass(),
                    'array' => ['array'],
                    'closure' => fn() => null,
                ],
            ],
        ]);
        $this->assertSame($definition, $container->get($id));
    }

    public function testSetAndGetObjectAndClosureDefinitionBasicUsage(): void
    {
        $container = new Container();

        $container->set($id = DummyData::class, $definition = new DummyData(new DummyName()));
        $this->assertSame($definition, $container->get($id));

        $container->set(DummyData::class, DummyData::class);
        $this->assertInstanceOf(DummyData::class, $container->get(DummyData::class));

        $container->set(DummyData::class, function () {
            return new DummyData(new DummyName());
        });
        $this->assertInstanceOf(DummyData::class, $container->get(DummyData::class));

        $container->set($id = 'name', function () {
            $dummyData = new DummyData(new DummyName());
            $dummyData->getName()->set('New Name');
            return $dummyData->getName();
        });

        $data = new DummyData(new DummyName());
        $data->getName()->set('New Name');
        $this->assertInstanceOf(DummyName::class, $container->get($id));
        $this->assertSame($data->getName()->get(), $container->get($id)->get());
    }

    public function testSetAndGetByPassingContainer(): void
    {
        $container = new Container();

        $container->set(DummyName::class, function () {
            return new DummyName('John');
        });

        $container->set('time', function () {
            return microtime(true);
        });

        $container->set(DummyData::class, function ($container) {
            /** @var Container $container */
            return new DummyData($container->get(DummyName::class), $container->get('time'));
        });

        $this->assertNotNull($instance = $container->get(DummyData::class));
        $this->assertSame($container->get(DummyName::class)->get(), $instance->getName()->get());
        $this->assertSame($container->get(DummyName::class), $instance->getName());
        $this->assertSame($container->get('time'), $instance->getTime());
        $this->assertSame($container->get(DummyData::class), $instance);
    }

    public function testGetSameObject(): void
    {
        $container = new Container();

        $container->set(DummyData::class, function () {
            return new DummyData((new DummyName('John')), microtime(true));
        });

        $this->assertNotNull($instance1 = $container->get(DummyData::class));
        $this->assertNotNull($instance2 = $container->get(DummyData::class));
        $this->assertSame($instance1->getName()->get(), $instance2->getName()->get());
        $this->assertSame($instance1->getName(), $instance2->getName());
        $this->assertSame($instance1->getTime(), $instance2->getTime());
        $this->assertSame($instance1, $instance2);
    }

    public function testGetSameObjectFromFactory(): void
    {
        $container = new Container();
        $container->set(DummyData::class, DummyFactory::class);

        $this->assertNotNull($instance1 = $container->get(DummyData::class));
        $this->assertNotNull($instance2 = $container->get(DummyData::class));
        $this->assertSame($instance1->getName()->get(), $instance2->getName()->get());
        $this->assertSame($instance1->getName(), $instance2->getName());
        $this->assertSame($instance1->getTime(), $instance2->getTime());
        $this->assertSame($instance1, $instance2);
    }

    public function testGetNewObject(): void
    {
        $container = new Container();

        $container->set(DummyData::class, function () {
            return new DummyData(new DummyName('John'), microtime(true));
        });

        $this->assertNotNull($instance1 = $container->getNew(DummyData::class));
        $this->assertNotNull($instance2 = $container->getNew(DummyData::class));
        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1->getName(), $instance2->getName());
        $this->assertNotSame($instance1->getTime(), $instance2->getTime());
        $this->assertSame($instance1->getName()->get(), $instance2->getName()->get());
    }

    public function testGetNewObjectFromFactory(): void
    {
        $container = new Container();
        $container->set(DummyData::class, DummyFactory::class);

        $this->assertNotNull($instance1 = $container->getNew(DummyData::class));
        $this->assertNotNull($instance2 = $container->getNew(DummyData::class));
        $this->assertNotSame($instance1, $instance2);
        $this->assertSame($instance1->getName(), $instance2->getName());
        $this->assertNotSame($instance1->getTime(), $instance2->getTime());
        $this->assertSame($instance1->getName()->get(), $instance2->getName()->get());
    }

    public function testConstructorWithPassDefinitions(): void
    {
        $container = new Container([
            $integerId = 'integer' => $integerDefinition = 5,
            $floatId = 'float' => $floatDefinition = 3.7,
            $booleanId = 'boolean' => $booleanDefinition = false,
            $stringId = 'string' => $stringDefinition = 'string',
            $arrayId = 'array' => $arrayDefinition = ['array'],
            $objectId = 'object' => $objectDefinition = new StdClass(),
            $closureId = 'closure' => fn() => null,
        ]);

        $this->assertSame($integerDefinition, $container->get($integerId));
        $this->assertSame($floatDefinition, $container->get($floatId));
        $this->assertSame($booleanDefinition, $container->get($booleanId));
        $this->assertSame($stringDefinition, $container->get($stringId));
        $this->assertSame($arrayDefinition, $container->get($arrayId));
        $this->assertSame($objectDefinition, $container->get($objectId));
        $this->assertNull($container->get($closureId));
    }

    public function testSetMultiple(): void
    {
        $container = new Container();
        $definitions = [
            $integerId = 'integer' => $integerDefinition = 5,
            $floatId = 'float' => $floatDefinition = 3.7,
            $booleanId = 'boolean' => $booleanDefinition = false,
            $stringId = 'string' => $stringDefinition = 'string',
            $arrayId = 'array' => $arrayDefinition = ['array'],
            $objectId = 'object' => $objectDefinition = new StdClass(),
            $closureId = 'closure' => fn() => null,
        ];

        $container->setMultiple($definitions);
        $this->assertSame($integerDefinition, $container->get($integerId));
        $this->assertSame($floatDefinition, $container->get($floatId));
        $this->assertSame($booleanDefinition, $container->get($booleanId));
        $this->assertSame($stringDefinition, $container->get($stringId));
        $this->assertSame($arrayDefinition, $container->get($arrayId));
        $this->assertSame($objectDefinition, $container->get($objectId));
        $this->assertNull($container->get($closureId));
    }

    public function testHas(): void
    {
        $container = new Container();

        $container->set('definitionId', 'definition');
        $this->assertTrue($container->has('definitionId'));
        $this->assertFalse($container->has('definitionNotExist'));
    }

    public function testGetDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'closure', fn() => null);
        $this->assertNull($container->get($id));
        $this->assertNotNull($container->getDefinition($id));
        $this->assertInstanceOf(Closure::class, $container->getDefinition($id));

        $container->set(StdClass::class, StdClass::class);
        $this->assertSame(StdClass::class, $container->getDefinition(StdClass::class));
    }

    public function testGetDefinitionThrowNotFoundForInvalidId(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get('definitionNotExist');
    }

    /**
     * @return array
     */
    public function invalidIdProvider(): array
    {
        return [
            'notExist' => ['definitionNotExist'],
            'object' => [new StdClass()],
            'closure' => [fn() => null],
            'false' => [false],
            'true' => [true],
            'null' => [null],
            'integer' => [1],
            'float' => [1.1],
            'array' => [[]],
        ];
    }

    /**
     * @dataProvider invalidIdProvider
     * @param mixed $id
     */
    public function testGetThrowNotFoundForInvalidId($id): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get($id);
    }

    public function testAutoInstantiatingWithoutUseSet(): void
    {
        $container = new Container();
        $this->assertInstanceOf(StdClass::class, $container->get(StdClass::class));
        $this->assertInstanceOf(DummyName::class, $container->get(DummyName::class));
        $this->assertInstanceOf(DummyData::class, $container->get(DummyData::class));
    }

    public function testAutoWiring(): void
    {
        $container = new Container();
        $autoWiring = $container->get(AutoWiring::class);

        $this->assertInstanceOf(DummyData::class, $autoWiring->getDummyData());
        $this->assertInstanceOf(DummyName::class, $autoWiring->getDummyData()->getName());
        $this->assertSame('Test Name', $autoWiring->getDummyData()->getName()->get());
        $this->assertNull($autoWiring->getDummyData()->getTime());

        $this->assertSame([], $autoWiring->getArray());
        $this->assertSame(100, $autoWiring->getInt());
        $this->assertSame('string', $autoWiring->getString());
    }

    public function testAutoWiringDummyFactory(): void
    {
        $container = new Container();
        $dummyData = $container->get(AutoWiringDummyFactory::class);

        $this->assertInstanceOf(DummyData::class, $dummyData);
        $this->assertInstanceOf(DummyName::class, $dummyData->getName());
        $this->assertSame('Test Name', $dummyData->getName()->get());
    }

    public function testAutoWiringScalarNotDefault(): void
    {
        $container = new Container();
        $this->expectException(ContainerException::class);
        $container->get(AutoWiringScalarNotDefault::class);
    }
}
