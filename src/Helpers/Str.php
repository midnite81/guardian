<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Helpers;

/**
 * Class Str
 *
 * A utility class for string manipulation operations.
 *
 * @internal This class is not part of the public API and may change without notice.
 */
class Str
{
    /**
     * Constructor.
     *
     * @param string $string The initial string to manipulate.
     */
    public function __construct(protected string $string)
    {
    }

    /**
     * Create a new instance of Str with the given string.
     *
     * @param string $string The string to manipulate.
     * @return Str
     */
    public static function of(string $string): Str
    {
        return new Str($string);
    }

    /**
     * Get the current string value.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->string;
    }

    /**
     * Convert the string to lowercase.
     *
     * @return $this
     */
    public function toLower(): Str
    {
        $this->string = strtolower($this->string);

        return $this;
    }

    /**
     * Remove duplicate occurrences of specified character(s).
     *
     * @param string|array<int, string> $characters The character(s) to remove duplicates of.
     * @return $this
     */
    public function removeDuplicateCharacters(string|array $characters = []): Str
    {
        if (is_string($characters)) {
            $characters = [$characters];
        }

        $pattern = '/(' . implode('|', array_map('preg_quote', $characters, array_fill(0, count($characters), '/'))) . ')\1+/u';
        $this->string = (string) preg_replace($pattern, '$1', $this->string);

        return $this;
    }

    /**
     * Remove the final character if it matches the specified character.
     *
     * @param string $character The character to remove if it's at the end.
     * @return $this
     */
    public function removeFinalCharIf(string $character): Str
    {
        $this->string = rtrim($this->string, '_');

        return $this;
    }

    /**
     * Limit the string to a specified number of characters.
     *
     * @param int $numberOfCharacters The maximum number of characters to keep.
     * @return $this
     */
    public function limit(int $numberOfCharacters): Str
    {
        $this->string = substr($this->string, 0, $numberOfCharacters);

        return $this;
    }

    /**
     * Modify the string using a custom callback function.
     *
     * @param callable $callback A function that takes a string and returns a modified string.
     * @return $this
     */
    public function modify(callable $callback): Str
    {
        $this->string = $callback($this->string);

        return $this;
    }
}
