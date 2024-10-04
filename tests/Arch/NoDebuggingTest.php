<?php

declare(strict_types=1);

arch('must not contain debugging functions')
    ->expect('Midnite81\Guardian')
    ->not->toUse(['ray', 'dd', 'dump']);
