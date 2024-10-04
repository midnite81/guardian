<?php

declare(strict_types=1);

use Midnite81\Guardian\Enums\Interval;

uses()->group('enums');

describe('Interval Enum', function () {
    it('has the correct cases', function () {
        expect(Interval::cases())->toHaveCount(6)
            ->and(Interval::SECOND->value)->toBe('second')
            ->and(Interval::MINUTE->value)->toBe('minute')
            ->and(Interval::HOUR->value)->toBe('hour')
            ->and(Interval::DAY->value)->toBe('day')
            ->and(Interval::WEEK->value)->toBe('week')
            ->and(Interval::MONTH->value)->toBe('month');
    });

    describe('toDays method', function () {
        it('converts SECOND to days', function () {
            expect(Interval::SECOND->toDays())->toBeCloseTo(1 / 86400, 1e-10);
        });

        it('converts MINUTE to days', function () {
            expect(Interval::MINUTE->toDays())->toBeCloseTo(1 / 1440, 1e-8);
        });

        it('converts HOUR to days', function () {
            expect(Interval::HOUR->toDays())->toBeCloseTo(1 / 24, 1e-6);
        });

        it('converts DAY to days', function () {
            expect(Interval::DAY->toDays())->toBeCloseTo(1, 1e-10);
        });

        it('converts WEEK to days', function () {
            expect(Interval::WEEK->toDays())->toBeCloseTo(7, 1e-10);
        });

        it('converts MONTH to days', function () {
            expect(Interval::MONTH->toDays())->toBeCloseTo(30.44, 1e-2);
        });
    });

    describe('toSeconds method', function () {
        it('converts SECOND to seconds', function () {
            expect(Interval::SECOND->toSeconds())->toBe(1);
        });

        it('converts MINUTE to seconds', function () {
            expect(Interval::MINUTE->toSeconds())->toBe(60);
        });

        it('converts HOUR to seconds', function () {
            expect(Interval::HOUR->toSeconds())->toBe(3600);
        });

        it('converts DAY to seconds', function () {
            expect(Interval::DAY->toSeconds())->toBe(86400);
        });

        it('converts WEEK to seconds', function () {
            expect(Interval::WEEK->toSeconds())->toBe(604800);
        });

        it('converts MONTH to seconds', function () {
            expect(Interval::MONTH->toSeconds())->toBe(2629746);
        });
    });
});
