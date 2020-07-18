<?php

declare(strict_types=1);

namespace Devanych\Tests\Di;

use Devanych\Di\Container;
use PHPUnit\Framework\TestCase;
use Devanych\Tests\Di\TestAsset\DummyData;
use Devanych\Tests\Di\TestAsset\DummyName;
use Devanych\Tests\Di\TestAsset\AutoWiring;
use Devanych\Di\Exception\NotFoundException;
use Devanych\Di\Exception\ContainerException;
use Devanych\Tests\Di\TestAsset\AutoWiringScalarNotDefault;

class ContainerTest extends TestCase
{
    public function testSetAndGetScalarDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'integer', $definition = 5);
        self::assertEquals($definition, $container->get($id));

        $container->set($id = 'float', $definition = 3.7);
        self::assertEquals($definition, $container->get($id));

        $container->set($id = 'boolean', $definition = false);
        self::assertEquals($definition, $container->get($id));

        $container->set($id = 'string', $definition = 'string');
        self::assertEquals($definition, $container->get($id));
    }

    public function testSetAndGetArrayDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'empty', $definition = []);
        self::assertEquals($definition, $container->get($id));

        $container->set($id = 'array', $definition = ['array']);
        self::assertEquals($definition, $container->get($id));

        $container->set($id = 'nested', $definition = [
            'nested' => [
                'scalar' => [
                    'integer' => 5,
                    'float' => 3.7,
                    'boolean' => false,
                    'string' => 'string',
                ],
                'not_scalar' => [
                    'object' => new \StdClass(),
                    'array' => ['array'],
                    'closure' => function () {
                        return;
                    },
                ],
            ],
        ]);
        self::assertEquals($definition, $container->get($id));
    }

    public function testSetAndGetObjectAndClosureDefinitionBasicUsage(): void
    {
        $container = new Container();

        $container->set($id = DummyData::class, $definition = new DummyData(new DummyName()));
        self::assertEquals($definition, $container->get($id));

        $container->set(DummyData::class, DummyData::class);
        self::assertInstanceOf(DummyData::class, $container->get(DummyData::class));

        $container->set(DummyData::class, function () {
            return new DummyData(new DummyName());
        });
        self::assertInstanceOf(DummyData::class, $container->get(DummyData::class));

        $container->set($id = 'name', function () {
            $dummyData = new DummyData(new DummyName());
            $dummyData->getName()->set('New Name');
            return $dummyData->getName();
        });

        $data = new DummyData(new DummyName());
        $data->getName()->set('New Name');
        self::assertInstanceOf(DummyName::class, $container->get($id));
        self::assertEquals($data->getName()->get(), $container->get($id)->get());
    }

    public function testSetAndGetByPassingContainer(): void
    {
        $container = new Container();

        $container->set(DummyName::class, function () {
            return new DummyName('John');
        });

        $container->set('time', function () {
            return \microtime(true);
        });

        $container->set(DummyData::class, function ($container) {
            /** @var Container $container */
            return new DummyData($container->get(DummyName::class), $container->get('time'));
        });

        self::assertNotNull($instance = $container->get(DummyData::class));
        self::assertEquals($container->get(DummyName::class)->get(), $instance->getName()->get());
        self::assertEquals($container->get(DummyName::class), $instance->getName());
        self::assertEquals($container->get('time'), $instance->getTime());
        self::assertEquals($container->get(DummyData::class), $instance);
    }

    public function testGetSameObject(): void
    {
        $container = new Container();

        $container->set(DummyData::class, function () {
            return new DummyData((new DummyName('John')), \microtime(true));
        });

        self::assertNotNull($instance1 = $container->get(DummyData::class));
        self::assertNotNull($instance2 = $container->get(DummyData::class));
        self::assertEquals($instance1->getName()->get(), $instance2->getName()->get());
        self::assertEquals($instance1->getName(), $instance2->getName());
        self::assertEquals($instance1->getTime(), $instance2->getTime());
        self::assertEquals($instance1, $instance2);
    }

    public function testGetNewObject(): void
    {
        $container = new Container();

        $container->set(DummyData::class, function () {
            return new DummyData(new DummyName('John'), \microtime(true));
        });

        self::assertNotNull($instance1 = $container->getNew(DummyData::class));
        self::assertNotNull($instance2 = $container->getNew(DummyData::class));
        self::assertEquals($instance1->getName()->get(), $instance2->getName()->get());
        self::assertEquals($instance1->getName(), $instance2->getName());
        self::assertNotEquals($instance1->getTime(), $instance2->getTime());
        self::assertNotEquals($instance1, $instance2);
    }

    public function testSetAll(): void
    {
        $container = new Container();
        $definitions = [
            $integerId = 'integer' => $integerDefinition = 5,
            $floatId = 'float' => $floatDefinition = 3.7,
            $booleanId = 'boolean' => $booleanDefinition = false,
            $stringId = 'string' => $stringDefinition = 'string',
            $arrayId = 'array' => $arrayDefinition = ['array'],
            $objectId = 'object' => $objectDefinition = new \StdClass(),
            $closureId = 'closure' => function () {
                return null;
            },
        ];

        $container->setAll($definitions);
        self::assertEquals($integerDefinition, $container->get($integerId));
        self::assertEquals($floatDefinition, $container->get($floatId));
        self::assertEquals($booleanDefinition, $container->get($booleanId));
        self::assertEquals($stringDefinition, $container->get($stringId));
        self::assertEquals($arrayDefinition, $container->get($arrayId));
        self::assertEquals($objectDefinition, $container->get($objectId));
        self::assertNull($container->get($closureId));
    }

    public function testHas(): void
    {
        $container = new Container();

        $container->set('definitionId', 'definition');
        self::assertTrue($container->has('definitionId'));
        self::assertFalse($container->has('definitionNotExist'));
    }

    public function testGetDefinition(): void
    {
        $container = new Container();

        $container->set($id = 'closure', function () {
            return null;
        });
        self::assertNull($container->get($id));
        self::assertNotNull($container->getDefinition($id));
        self::assertInstanceOf(\Closure::class, $container->getDefinition($id));

        $container->set(\StdClass::class, \StdClass::class);
        self::assertEquals(\StdClass::class, $container->getDefinition(\StdClass::class));
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
            'object' => [new \StdClass()],
            'closure' => [function () {
                return;
            }],
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
        self::assertInstanceOf(\StdClass::class, $container->get(\StdClass::class));
        self::assertInstanceOf(DummyName::class, $container->get(DummyName::class));
        self::assertInstanceOf(DummyData::class, $container->get(DummyData::class));
    }

    public function testAutoWiring(): void
    {
        $container = new Container();

        /** @var AutoWiring $parent */
        self::assertNotNull($parent = $container->get(AutoWiring::class));

        self::assertInstanceOf(DummyData::class, $parent->getDummyData());
        self::assertInstanceOf(DummyName::class, $parent->getDummyData()->getName());
        self::assertEquals('Test Name', $parent->getDummyData()->getName()->get());
        self::assertNull($parent->getDummyData()->getTime());

        self::assertEquals([], $parent->getArray());
        self::assertEquals(100, $parent->getInt());
        self::assertEquals('string', $parent->getString());
    }

    public function testAutoWiringScalarNotDefault(): void
    {
        $container = new Container();
        $this->expectException(ContainerException::class);
        $container->get(AutoWiringScalarNotDefault::class);
    }
}
