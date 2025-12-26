<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YourPackage;

use Cline\Morphism\Concerns\ConfiguresMorphism;
use Cline\Morphism\MorphKeyRegistry;
use Illuminate\Support\ServiceProvider;

use function now;

/**
 * Example: Your package's service provider
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class YourPackageServiceProvider extends ServiceProvider
{
    // Include the trait for config-based morph key setup
    use ConfiguresMorphism;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/your-package.php', 'your-package');
    }

    public function boot(): void
    {
        // This reads 'your-package.morphKeyMap' and 'your-package.enforceMorphKeyMap'
        // from your package's config file and applies them to the shared registry
        $this->configureMorphism('your-package');
    }
}

/**
 * Example: Using the registry in your package's service class
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class YourService
{
    public function __construct(
        private readonly MorphKeyRegistry $registry,
    ) {}

    public function storePolymorphicRelation(Model $model): void
    {
        // Get the correct key column for this model type
        $keyColumn = $this->registry->getKey($model);
        $keyValue = $this->registry->getValue($model);

        // Store in your pivot table
        DB::table('your_polymorphic_table')->insert([
            'related_type' => $model->getMorphClass(),
            'related_id' => $keyValue,  // Uses the correct key (uuid, ulid, or id)
            'created_at' => now(),
        ]);
    }

    public function resolvePolymorphicRelation(string $type, string|int $id): ?Model
    {
        // Get the key column for this model type
        $keyColumn = $this->registry->getKeyFromClass($type);

        // Query using the correct key column
        return $type::where($keyColumn, $id)->first();
    }
}

/**
 * Example: Your package's config file (config/your-package.php)
 */
return [
    // ... other config options ...

    'morphKeyMap' => [
        // Users can configure their model key mappings here
        // App\Models\User::class => 'uuid',
    ],
    'enforceMorphKeyMap' => [
        // Or use enforced mappings for strict mode
        // App\Models\User::class => 'uuid',
    ],
];
