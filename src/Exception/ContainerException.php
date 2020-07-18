<?php declare(strict_types=1);

namespace Devanych\Di\Exception;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends LogicException implements ContainerExceptionInterface
{
}
