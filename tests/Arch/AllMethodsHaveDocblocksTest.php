<?php

declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Assert;
use Symfony\Component\Finder\Finder;

arch('all methods must be docblocked')
    ->expect('Midnite81\Guardian')
    ->toHaveMethodsDocumented();

arch('all non-constructor methods defined in classes have docblocks', function () {
    $srcDir = __DIR__ . '/../../src';

    $parser = (new ParserFactory)->createForNewestSupportedVersion();
    $nodeFinder = new NodeFinder;

    $finder = new Finder;
    $finder->files()->in($srcDir)->name('*.php');

    $methodsWithoutDocblocks = [];

    foreach ($finder as $file) {
        $path = $file->getRealPath();
        $code = file_get_contents($path);

        try {
            $ast = $parser->parse($code);

            $classLikes = $nodeFinder->findInstanceOf($ast, Class_::class);
            $classLikes = array_merge($classLikes, $nodeFinder->findInstanceOf($ast, Interface_::class));
            $classLikes = array_merge($classLikes, $nodeFinder->findInstanceOf($ast, Trait_::class));

            foreach ($classLikes as $classLike) {
                $className = $classLike->name ? $classLike->name->toString() : 'Anonymous Class';

                foreach ($classLike->getMethods() as $method) {
                    $methodName = $method->name ? $method->name->toString() : 'Unknown Method';

                    if ($methodName !== '__construct' && $method->getDocComment() === null) {
                        $methodsWithoutDocblocks[] = [
                            'name' => $methodName,
                            'class' => $className,
                            'line' => $method->getStartLine(),
                            'file' => $path,
                        ];
                    }
                }
            }

        } catch (\Throwable $e) {
            Assert::fail("Error processing file $path: " . $e->getMessage());
        }
    }

    $failureMessages = array_map(function ($method) {
        return "Method '{$method['name']}' in {$method['class']} (line {$method['line']}) in file '{$method['file']}' is missing a docblock";
    }, $methodsWithoutDocblocks);

    expect($methodsWithoutDocblocks)->toBeEmpty(implode("\n", $failureMessages));
});
