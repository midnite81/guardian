<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Exceptions;

use Exception;

/**
 * Exception thrown when an identifier is empty.
 *
 * This exception is used to indicate that an identifier, which is required
 * to be non-empty, has been provided as an empty value.
 */
class IdentifierCannotBeEmptyException extends Exception
{
}
