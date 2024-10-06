<?php

declare(strict_types=1);

use Midnite81\Guardian\Helpers\Str;

it('creates a new instance with a given string', function () {
    $str = Str::of('Hello World');
    expect($str)->toBeInstanceOf(Str::class);
    expect($str->toString())->toBe('Hello World');
});

it('converts string to lowercase', function () {
    $str = Str::of('HELLO WORLD');
    expect($str->toLower()->toString())->toBe('hello world');
});

it('removes duplicate characters', function () {
    $str = Str::of('aabbccddee');
    expect($str->removeDuplicateCharacters(['a', 'b'])->toString())->toBe('abccddee');

    $str = Str::of('aabbccddee');
    expect($str->removeDuplicateCharacters('a')->toString())->toBe('abbccddee');

    $str = Str::of('a__b__c__');
    expect($str->removeDuplicateCharacters('_')->toString())->toBe('a_b_c_');
});

it('removes final character if it matches', function () {
    $str = Str::of('Hello_');
    expect($str->removeFinalCharIf('_')->toString())->toBe('Hello');

    $str = Str::of('Hello');
    expect($str->removeFinalCharIf('_')->toString())->toBe('Hello');
});

it('limits the string to specified number of characters', function () {
    $str = Str::of('Hello World');
    expect($str->limit(5)->toString())->toBe('Hello');

    $str = Str::of('Hi');
    expect($str->limit(5)->toString())->toBe('Hi');
});

it('modifies the string using a custom callback', function () {
    $str = Str::of('hello world');
    $result = $str->modify(function ($string) {
        return strtoupper($string);
    });
    expect($result->toString())->toBe('HELLO WORLD');
});

it('chains multiple operations', function () {
    $str = Str::of('HELLO__WORLD__');
    $result = $str->toLower()
        ->removeDuplicateCharacters('_')
        ->removeFinalCharIf('_')
        ->limit(10);
    expect($result->toString())->toBe('hello_worl');
});
