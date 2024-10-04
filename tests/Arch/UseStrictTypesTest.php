<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

arch('application must use strict types')
    ->expect('Midnite81\Guardian')
    ->toUseStrictTypes();

arch('tests must use strict types', function () {
    pest_files_in_path(__DIR__ . '/../', function ($file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            if (!str_contains($content, 'declare(strict_types=1);')) {
                Assert::fail("File {$file->getPathname()} does not declare strict types.");
            }
        }
    });

    Assert::assertTrue(true, 'tests use strict types');
});
