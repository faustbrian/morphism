<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Models\Organization;
use App\Models\User;
use Cline\Morphism\MorphKeyRegistry;

// Get the registry from the container (it's a singleton)
$registry = app(MorphKeyRegistry::class);

// Register key mappings for your models
$registry->map([
    User::class => 'uuid',           // User model uses 'uuid' as primary key
    Organization::class => 'ulid',   // Organization uses 'ulid'
]);

// Get the key column name for a model
$user = User::find('550e8400-e29b-41d4-a716-446655440000');
$keyColumn = $registry->getKey($user);  // Returns 'uuid'

// Get the actual key value
$keyValue = $registry->getValue($user);  // Returns '550e8400-e29b-41d4-a716-446655440000'

// Check if a model has a registered mapping
if ($registry->has(User::class)) {
    // Model has explicit key mapping
}

// Get key from class string (without instantiating)
$keyColumn = $registry->getKeyFromClass(Organization::class);  // Returns 'ulid'

// Get all registered mappings
$allMappings = $registry->all();
// Returns: [User::class => 'uuid', Organization::class => 'ulid']
