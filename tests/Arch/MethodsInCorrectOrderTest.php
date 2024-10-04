<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;
use Symfony\Component\Finder\Finder;

arch('methods are in correct visibility order for all classes and interfaces in src', function () {
    $classesAndInterfaces = getClassesAndInterfacesInSrc();
    Assert::assertGreaterThan(0, count($classesAndInterfaces), 'No classes or interfaces found to test.');

    foreach ($classesAndInterfaces as $name) {
        try {
            $reflection = new ReflectionClass($name);
        } catch (ReflectionException $e) {
            Assert::fail("Error creating Reflection for $name: " . $e->getMessage());
        }

        $methods = array_filter(
            $reflection->getMethods(),
            function ($method) use ($name) {
                return $method->getDeclaringClass()->getName() === $name
                    && $method->getName() !== '__construct';
            }
        );

        if (empty($methods)) {
            continue; // Skip classes with no methods (excluding constructor)
        }

        if ($reflection->isInterface()) {
            Assert::assertTrue(true, "$name is an interface, skipping visibility order check.");

            continue;
        }

        // For classes, check visibility order
        $visibilityOrder = array_map(function ($method) {
            if ($method->isPublic()) {
                return 'public';
            }
            if ($method->isProtected()) {
                return 'protected';
            }
            if ($method->isPrivate()) {
                return 'private';
            }

            return '';
        }, $methods);

        // Check if the class is likely a Facade
        $isFacade = $reflection->isSubclassOf(\Illuminate\Support\Facades\Facade::class);

        if ($isFacade && count(array_unique($visibilityOrder)) === 1 && $visibilityOrder[0] === 'protected') {
            Assert::assertTrue(true, "$name is a Facade with only protected methods, which is allowed.");
        } else {
            $hasPublicMethods = in_array('public', $visibilityOrder);
            $hasProtectedMethods = in_array('protected', $visibilityOrder);
            $hasPrivateMethods = in_array('private', $visibilityOrder);

            if ($hasPublicMethods) {
                $lastPublic = count($visibilityOrder) - 1 - array_search('public', array_reverse($visibilityOrder));
            }

            if ($hasProtectedMethods) {
                $firstProtected = array_search('protected', $visibilityOrder);
                if ($hasPublicMethods) {
                    Assert::assertTrue(
                        $firstProtected > $lastPublic,
                        "In $name, a protected method appears before the last public method."
                    );
                }
            }

            if ($hasPrivateMethods) {
                $firstPrivate = array_search('private', $visibilityOrder);
                if ($hasProtectedMethods) {
                    Assert::assertTrue(
                        $firstPrivate > $firstProtected,
                        "In $name, a private method appears before a protected method."
                    );
                }
                if ($hasPublicMethods) {
                    Assert::assertTrue(
                        $firstPrivate > $lastPublic,
                        "In $name, a private method appears before the last public method."
                    );
                }
            }
        }

        Assert::assertTrue(true, "$name passed the visibility order test.");
    }
});

function getClassesAndInterfacesInSrc(): array
{
    try {
        $finder = new Finder;
        $finder->files()->in(__DIR__ . '/../../src')->name('*.php');

        $classesAndInterfaces = [];
        foreach ($finder as $file) {
            $namespace = getNamespace($file);
            $name = $namespace . '\\' . $file->getBasename('.php');
            if (class_exists($name) || interface_exists($name)) {
                $classesAndInterfaces[] = $name;
            }
        }

        return $classesAndInterfaces;
    } catch (Exception $e) {
        Assert::fail('Error in getClassesAndInterfacesInSrc: ' . $e->getMessage());
    }
}

function getNamespace($file): string
{
    try {
        $src = file_get_contents($file->getRealPath());
        preg_match('/namespace\s+(.+?);/', $src, $matches);

        return $matches[1] ?? '';
    } catch (Exception $e) {
        Assert::fail('Error reading file ' . $file->getPathname() . ': ' . $e->getMessage());
    }
}
