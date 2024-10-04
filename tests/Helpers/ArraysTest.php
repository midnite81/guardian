<?php

declare(strict_types=1);

use Midnite81\Guardian\Helpers\Arrays;
use Midnite81\Guardian\Tests\Helpers\Fixtures\CustomTestException;

// Test valid array
it('passes with a valid array', function () {
    $array = [new stdClass, new stdClass];

    Arrays::mustBeInstanceOf($array, stdClass::class);
})->throwsNoExceptions();

// Test invalid item in array
it('throws exception with invalid item in array', function () {
    $array = [new stdClass, 'not an object'];

    Arrays::mustBeInstanceOf($array, stdClass::class);
})->throws(InvalidArgumentException::class, 'Each item in the array must be an instance of stdClass');

// Test custom exception class
it('throws custom exception class', function () {
    $array = [new stdClass, 'not an object'];

    Arrays::mustBeInstanceOf($array, stdClass::class, null, 'Custom exception message', 0, CustomTestException::class);
})->throws(CustomTestException::class, 'Custom exception message');

// Test callback functionality
it('calls callback for invalid item', function () {
    $callbackCalled = false;
    $callback = function ($item, $array) use (&$callbackCalled) {
        $callbackCalled = true;
        expect($item)->not->toBeInstanceOf(stdClass::class)
            ->and($array)->toContain($item);
    };

    $array = [new stdClass, 'not an object'];

    Arrays::mustBeInstanceOf($array, stdClass::class, $callback);

    expect($callbackCalled)->toBeTrue();
});

// Test empty array
it('passes with an empty array', function () {
    $array = [];

    Arrays::mustBeInstanceOf($array, stdClass::class);
})->throwsNoExceptions();

// Test custom error message
it('throws exception with custom error message', function () {
    $array = [new stdClass, 'not an object'];

    Arrays::mustBeInstanceOf($array, stdClass::class, null, 'Custom error: must be %s');
})->throws(InvalidArgumentException::class, 'Custom error: must be stdClass');

// Test with multiple invalid items
it('throws exception on first invalid item', function () {
    $array = [new stdClass, 'not an object', 123, false];

    Arrays::mustBeInstanceOf($array, stdClass::class);
})->throws(InvalidArgumentException::class);

// Test with all valid items of different classes
it('passes with all valid items of different classes', function () {
    class TestClass1
    {
    }
    class TestClass2 extends TestClass1
    {
    }

    $array = [new TestClass1, new TestClass2];

    Arrays::mustBeInstanceOf($array, TestClass1::class);
})->throwsNoExceptions();

// Test callback receives correct arguments
it('provides correct arguments to callback', function () {
    $invalidItem = 'not an object';
    $array = [new stdClass, $invalidItem];

    $callback = function ($item, $receivedArray) use ($invalidItem, $array) {
        expect($item)->toBe($invalidItem)
            ->and($receivedArray)->toBe($array);
    };

    Arrays::mustBeInstanceOf($array, stdClass::class, $callback);
});
