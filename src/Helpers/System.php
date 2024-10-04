<?php

declare(strict_types=1);

namespace Midnite81\Guardian\Helpers;

/**
 * Helper class for system-related operations.
 *
 * @internal This class is not meant to be used outside of the Midnite81\Guardian package.
 */
class System
{
    /**
     * Create a directory.
     *
     * @param string $path The path of the directory to create.
     * @param int $permissions The permissions to set for the new directory.
     * @param bool $recursive Whether to create parent directories if they don't exist.
     * @return bool True on success, false on failure.
     */
    public function mkdir(string $path, int $permissions = 0755, bool $recursive = true): bool
    {
        return mkdir($path, $permissions, $recursive);
    }

    /**
     * Check if a directory exists.
     *
     * @param string $path The path to check.
     * @return bool True if the directory exists, false otherwise.
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Read the contents of a file.
     *
     * @param string $filename The name of the file to read.
     * @return string|false The file contents on success, or false on failure.
     */
    public function fileGetContents(string $filename): string|false
    {
        return file_get_contents($filename);
    }

    /**
     * Write contents to a file.
     *
     * @param string $filename The name of the file to write to.
     * @param string $data The data to write.
     * @return int|false The number of bytes written on success, or false on failure.
     */
    public function filePutContents(string $filename, string $data): int|false
    {
        return file_put_contents($filename, $data);
    }

    /**
     * Delete a file.
     *
     * @param string $filename The name of the file to delete.
     * @return bool True on success, false on failure.
     */
    public function unlink(string $filename): bool
    {
        return unlink($filename);
    }

    /**
     * Check if a file exists.
     *
     * @param string $filename The name of the file to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * Get the last error that occurred.
     *
     * @return array<string, int|string>|null An array containing error information or null if no error occurred.
     *
     * @codeCoverageIgnore
     */
    public function errorGetLast(): ?array
    {
        return error_get_last();
    }
}
