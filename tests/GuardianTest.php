<?php

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Midnite81\Guardian\Exceptions\IdentifierCannotBeEmptyException;
use Midnite81\Guardian\Exceptions\RateLimitExceededException;
use Midnite81\Guardian\Exceptions\RulePreventsExecutionException;
use Midnite81\Guardian\Guardian;
use Midnite81\Guardian\Rules\ErrorHandlingRule;
use Midnite81\Guardian\Rules\RateLimitRule;
use Midnite81\Guardian\Store\LaravelStore;
use Midnite81\Guardian\Tests\Fixtures\CustomErrorRules;
use Midnite81\Guardian\Tests\OrchestraTestCase;

uses(OrchestraTestCase::class)->group('guardian');

beforeEach(function () {
    Cache::flush();
});

it('can be instantiated', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    expect($guardian)->toBeInstanceOf(Guardian::class);
});

it('throws an exception for empty identifier', function () {
    new Guardian('', new LaravelStore(app('cache.store')));
})->throws(IdentifierCannotBeEmptyException::class);

it('sanitizes the identifier', function () {
    $guardian = new Guardian('test!@#$%^&*()', new LaravelStore(app('cache.store')));
    expect($guardian->getIdentifier())->toBe('guardian_test');
});

it('can add rate limit rules', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addRules([
        RateLimitRule::allow(5)->perMinute(),
    ]);
    expect($guardian->getRules())->not->toBeNull();
});

it('can add rate limit rules after setting', function () {
    $guardian = new Guardian(
        'test',
        new LaravelStore(app('cache.store')),
        new \Midnite81\Guardian\Tests\Fixtures\CustomRules
    );
    $guardian->addRules([
        RateLimitRule::allow(5)->perMinute(),
    ]);

    expect($guardian->getRules())->not->toBeNull()
        ->and($guardian->getRules())->toBeInstanceOf(\Midnite81\Guardian\Tests\Fixtures\CustomRules::class)
        ->and($guardian->getRules()->getRules())->toHaveCount(2);
});

it('can add error handling rules', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addErrorRules([
        ErrorHandlingRule::allowFailures(3)->perMinute(),
    ]);
    expect($guardian->getErrorRules())->not->toBeNull();
});

it('can add error handling rules when already set', function () {
    $guardian = new Guardian(
        'test',
        new LaravelStore(app('cache.store')),
        errorRules: new CustomErrorRules);

    $guardian->addErrorRules([
        ErrorHandlingRule::allowFailures(3)->perMinute(),
    ]);

    expect($guardian->getErrorRules()->getRules())->toHaveCount(2);
});

it('allows execution when no rules are set', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $result = $guardian->send(function () {
        return 'success';
    });
    expect($result)->toBe('success');
});

it('allows execution when within rate limit', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addRules([
        RateLimitRule::allow(5)->perMinute(),
    ]);

    for ($i = 0; $i < 5; $i++) {
        $result = $guardian->send(function () {
            return 'success';
        });
        expect($result)->toBe('success');
    }
});

it('prevents execution when rate limit is exceeded', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addRules([
        RateLimitRule::allow(2)->perMinute(),
    ]);

    $guardian->send(fn () => 'success');
    $guardian->send(fn () => 'success');

    expect(fn () => $guardian->send(fn () => 'success'))
        ->toThrow(
            RulePreventsExecutionException::class,
            'Cannot execute the request. Rate limit exceeded: 2 requests per 1 minute.'
        );
});

it('prevents execution when rate limit is exceeded but returns null', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addRules([
        RateLimitRule::allow(2)->perMinute(),
    ]);

    $guardian->send(fn () => 'success', false);
    $guardian->send(fn () => 'success', false);

    expect($guardian->send(fn () => 'success', false))->toBeNull();
});

it('handles errors according to error rules', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addErrorRules([
        ErrorHandlingRule::allowFailures(2)->perMinute()->thenThrow(false),
    ]);

    $guardian->send(fn () => throw new Exception('Test exception'));
    $guardian->send(fn () => throw new Exception('Test exception'));

    $result = $guardian->send(fn () => throw new Exception('Test exception'));

    expect($result)->toBeNull();
});

it('handles errors according to error rules to throw', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));
    $guardian->addErrorRules([
        ErrorHandlingRule::allowFailures(2)->perMinute()->thenThrow(),
    ]);

    $guardian->send(fn () => throw new Exception('Test exception'));
    $guardian->send(fn () => throw new Exception('Test exception'));

    expect(fn () => $guardian->send(fn () => throw new Exception('Test exception')))->toThrow(Exception::class);
});

it('sets and clears cache for the instance', function () {
    $cache = new LaravelStore(app('cache.store'));
    $rule = RateLimitRule::allow(5)->perMinute();
    $guardian = new Guardian('test', $cache, [
        $rule,
    ]);

    $cacheKey = $guardian->getCacheKey($rule);
    $guardian->send(fn () => 'success');

    expect($cache->has($cacheKey))->toBeTrue();

    $guardian->clearCache();

    expect($cache->has($cacheKey))->toBeFalse();
});

it('returns false if not each key is cleared from the cache', function () {
    $cache = Mockery::mock(\Midnite81\Guardian\Contracts\Store\CacheInterface::class);

    $rules = [
        RateLimitRule::allow(5)->perMinute(),
        RateLimitRule::allow(500)->perHour(),
    ];

    $errorRules = [
        ErrorHandlingRule::allowFailures(2)->perMinute()->thenThrow(),
    ];

    $cache->shouldReceive('forget')->with('guardian_test:rate_limit_5_per_minute')->andReturn(false);
    $cache->shouldReceive('forget')->with('guardian_test:rate_limit_500_per_hour')->andReturn(true);
    $cache->shouldReceive('forget')->with('guardian_test:error:2_minute_1_no_expiry')->andReturn(true);

    $guardian = new Guardian('test', $cache, $rules, $errorRules);

    expect($guardian->clearCache())->toBeFalse();
});

it('uses custom cache prefix', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')), null, null, 'custom_prefix');
    $guardian->addRules([
        RateLimitRule::allow(5)->perMinute(),
    ]);

    $guardian->send(fn () => 'success');

    expect(Cache::has('custom_prefix_test:rate_limit_5_per_minute'))->toBeTrue();
});

it('can update properties via set methods', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));

    $guardian->setCache(new \Midnite81\Guardian\Store\FileStore(__DIR__))
        ->setRules([
            RateLimitRule::allow(5)->perMinute(),
        ])
        ->setErrorRules([
            ErrorHandlingRule::allowFailures(2)->perMinutes(15)->thenThrow(),
        ]);

    expect($guardian->getCache())->toBeInstanceOf(\Midnite81\Guardian\Store\FileStore::class)
        ->and($guardian->getRules())->toBeInstanceOf(\Midnite81\Guardian\Rulesets\GenericRateLimitingRuleset::class)
        ->and($guardian->getErrorRules())
        ->toBeInstanceOf(\Midnite81\Guardian\Rulesets\GenericErrorHandlingRuleset::class);

    rmdir(__DIR__ . '/guardian_cache');
});

it('cannot have a blank identifier', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));

    expect(fn () => $guardian->setIdentifier('', ''))
        ->toThrow(IdentifierCannotBeEmptyException::class, 'Identifier cannot be empty')
        ->and(fn () => $guardian->setIdentifier('id_', ''))
        ->toThrow(IdentifierCannotBeEmptyException::class, 'Identifier cannot be empty');
});

it('must make safe an identifer starting with a number', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));

    $guardian->setIdentifier('001', '');
    expect($guardian->getIdentifier())->toBe('id_001');
});

it('throws exception by default when no error rules are set', function () {
    $guardian = new Guardian('test', new LaravelStore(app('cache.store')));

    $guardian->send(function () {
        throw new Exception('Test exception');
    });
})->throws(Exception::class, 'Test exception');

/******/

it('passes through RateLimitExceededException when thrown in callback', function () {
    $guardian = new Guardian(
        'test',
        new LaravelStore(app('cache.store')),
        [
            RateLimitRule::allow(5)->perMinute(),
        ]
    );

    $retryAfter = new DateTimeImmutable('+1 minute');
    $exceptionMessage = 'Rate limit exceeded in callback';

    expect(fn () => $guardian->send(function () use ($retryAfter, $exceptionMessage) {
        throw new RateLimitExceededException($retryAfter, $exceptionMessage);
    }))->toThrow(RateLimitExceededException::class, $exceptionMessage);
});

it('continues to throw RateLimitExceededException after initial exception from callback', function () {
    $guardian = new Guardian(
        'test',
        new LaravelStore(app('cache.store')),
        [
            RateLimitRule::allow(5)->perMinute(),
        ]
    );

    $retryAfter = new DateTimeImmutable('+1 minute');
    $exceptionMessage = 'Rate limit exceeded in callback';

    // First call: throws RateLimitExceededException from callback
    expect(fn () => $guardian->send(function () use ($retryAfter, $exceptionMessage) {
        throw new RateLimitExceededException($retryAfter, $exceptionMessage);
    }))->toThrow(RateLimitExceededException::class, $exceptionMessage);

    // Second call: should still throw RateLimitExceededException, but from Guardian itself
    expect(fn () => $guardian->send(fn () => 'This should not execute'))
        ->toThrow(RateLimitExceededException::class);

    // Verify that the retry after time is respected
    expect($guardian->isRateLimitExceeded())->toBeTrue();
    expect($guardian->getRateLimitRetryAfter())->toEqual($retryAfter);
});
