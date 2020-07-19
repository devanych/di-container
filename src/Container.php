<?php

declare(strict_types=1);

namespace Devanych\Di;

use Closure;
use Devanych\Di\Exception\NotFoundException;
use Devanych\Di\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use Throwable;

use function array_key_exists;
use function class_exists;
use function gettype;
use function in_array;
use function is_string;
use function sprintf;

final class Container implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @param array $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->setAll($definitions);
    }

    /**
     * Sets definition to the container.
     *
     * @param string $id
     * @param mixed $definition
     */
    public function set(string $id, $definition): void
    {
        if ($this->hasInstance($id)) {
            unset($this->instances[$id]);
        }

        $this->definitions[$id] = $definition;
    }

    /**
     * Sets multiple definitions at once.
     *
     * @param array $definitions
     */
    public function setAll(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->checkIdIsStringType($id);
            $this->set($id, $definition);
        }
    }

    /**
     * Gets instance by definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException If not found definition in the container.
     * @throws ContainerException If unable to create instance.
     */
    public function get($id)
    {
        $this->checkIdIsStringType($id);

        if ($this->hasInstance($id)) {
            return $this->instances[$id];
        }

        $this->instances[$id] = $this->createInstance($id);
        return $this->instances[$id];
    }

    /**
     * Always gets a new instance by definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException If not found definition in the container.
     * @throws ContainerException If unable to create instance.
     */
    public function getNew(string $id)
    {
        return $this->createInstance($id);
    }

    /**
     * Gets original definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException If not found definition in the container.
     */
    public function getDefinition(string $id)
    {
        if ($this->has($id)) {
            return $this->definitions[$id];
        }

        throw new NotFoundException(sprintf('`%s` is not set in container.', $id));
    }

    /**
     * Returns `true` if the container can return an definition for this ID, otherwise `false`.
     *
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * Create instance by definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException If not found definition in the container.
     * @throws ContainerException If unable to create instance.
     */
    private function createInstance(string $id)
    {
        if (!$this->has($id)) {
            if ($this->isClassName($id)) {
                return $this->createObject($id);
            }

            throw new NotFoundException(sprintf('`%s` is not set in container and is not a class name.', $id));
        }

        if ($this->isClassName($this->definitions[$id])) {
            return $this->createObject($this->definitions[$id]);
        }

        if ($this->definitions[$id] instanceof Closure) {
            return $this->definitions[$id]($this);
        }

        return $this->definitions[$id];
    }

    /**
     * Create object by class name.
     *
     * @param string $className
     * @return object
     * @throws ContainerException If unable to create object.
     */
    private function createObject(string $className): object
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException(sprintf('Unable to create object `%s`.', $className), 0, $e);
        }

        if (in_array(FactoryInterface::class, $reflection->getInterfaceNames())) {
            try {
                /** @var FactoryInterface $factory */
                $factory = $this->getObjectFromReflection($reflection);
                return $factory->create($this);
            } catch (ContainerException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw new ContainerException(sprintf('Unable to create object `%s`.', $className), 0, $e);
            }
        }

        return $this->getObjectFromReflection($reflection);
    }

    /**
     * Create object from reflection.
     *
     * If the object has dependencies in the constructor, it tries to create them too.
     *
     * @param ReflectionClass $reflection
     * @return object
     * @throws ContainerException If unable to create object.
     */
    private function getObjectFromReflection(ReflectionClass $reflection): object
    {
        if (($constructor = $reflection->getConstructor()) === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameterClass = $parameter->getClass()) {
                $arguments[] = $this->get($parameterClass->getName());
                continue;
            }

            if ($parameter->isArray()) {
                $arguments[] = [];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                try {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                } catch (ReflectionException $e) {
                    throw new ContainerException(sprintf(
                        'Unable to create object `%s`. Unable to get default value of constructor parameter: `%s`.',
                        $reflection->getName(),
                        $parameter->getName()
                    ));
                }
            }

            throw new ContainerException(sprintf(
                'Unable to create object `%s`. Unable to process a constructor parameter: `%s`.',
                $reflection->getName(),
                $parameter->getName()
            ));
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Returns `true` if the container can return an instance for this ID, otherwise `false`.
     *
     * @param string $id
     * @return bool
     */
    private function hasInstance(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Returns `true` if `$className` is the class name, otherwise `false`.
     *
     * @param mixed $className
     * @return bool
     */
    private function isClassName($className): bool
    {
        return (is_string($className) && class_exists($className));
    }

    /**
     * @param mixed $id
     * @throws NotFoundException for not string types.
     */
    private function checkIdIsStringType($id): void
    {
        if (!is_string($id)) {
            throw new NotFoundException(sprintf(
                'Is not valid ID. Must be string type; received `%s`.',
                gettype($id)
            ));
        }
    }
}
