<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;

/**
 * Cookbook: Configuration-Based Setup
 *
 * This cookbook demonstrates how to configure morph key mappings
 * via Laravel's configuration system instead of programmatically.
 */

// In config/morphism.php (publish with: php artisan vendor:publish --tag=morphism-config)
return [
    // Option 1: Non-enforced mappings (unmapped models use their default key)
    'morphKeyMap' => [
        User::class => 'uuid',
        Organization::class => 'ulid',
        Team::class => 'id',
    ],
    // Option 2: Enforced mappings (unmapped models throw exception)
    // Note: Only use ONE of these options, not both!
    'enforceMorphKeyMap' => [
        // App\Models\User::class => 'uuid',
        // App\Models\Organization::class => 'ulid',
    ],
];

// The MorphismServiceProvider automatically reads this config on boot.
// No additional code needed - just publish and configure!
