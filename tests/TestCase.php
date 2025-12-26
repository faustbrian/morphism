<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Cline\Morphism\MorphismServiceProvider;
use Cline\Morphism\MorphKeyRegistry;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Reset the registry between tests.
     */
    #[Override()]
    protected function tearDown(): void
    {
        $this->app?->make(MorphKeyRegistry::class)->reset();

        parent::tearDown();
    }

    /**
     * @param  Application              $app
     * @return array<int, class-string>
     */
    #[Override()]
    protected function getPackageProviders($app): array
    {
        return [
            MorphismServiceProvider::class,
        ];
    }
}
