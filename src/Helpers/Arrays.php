<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Helpers;

use Closure;
use Exception;
use InvalidArgumentException;
use stdClass;

/**
 * Helper class for array operations.
 *
 * @internal This class is not meant to be used outside of the Midnite81\Guardian package.
 */
class Arrays
{
    /**
     * Ensures that each item in the given array is an instance of the specified class.
     *
     * @param array<mixed, mixed> $array The array of items to check.
     * @param class-string $class The class name to check against.
     * @param Closure|null $callback A closure to execute if an item is not an instance of the specified class.
     * @param string $throwMessage The message to use if an exception is thrown.
     * @param int $throwCode The code to use if an exception is thrown.
     * @param class-string<Exception> $throwClass The class of the exception to throw.
     *
     * @throws InvalidArgumentException If an item is not an instance of the specified class and no callback is provided.
     * @throws Exception If a custom exception class is specified and an item is not an instance of the specified class.
     */
    public static function mustBeInstanceOf(
        array $array,
        string $class = stdClass::class,
        ?Closure $callback = null,
        string $throwMessage = 'Each item in the array must be an instance of %s',
        int $throwCode = 0,
        string $throwClass = InvalidArgumentException::class
    ): void {
        $throwMessage = sprintf($throwMessage, $class);

        foreach ($array as $item) {
            if (!$item instanceof $class) {
                if ($callback instanceof Closure) {
                    $callback($item, $array);
                } elseif ($throwClass !== InvalidArgumentException::class) {
                    throw new $throwClass($throwMessage, $throwCode);
                } else {
                    throw new InvalidArgumentException($throwMessage, $throwCode);
                }
            }
        }
    }
}
