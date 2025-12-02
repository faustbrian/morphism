<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Morphism\Enums\MorphType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Example migration using morphism() macro.
 *
 * The macro automatically uses the configured default morph type
 * from config/morphism.php (defaults to ULID).
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->text('body');

            // Creates commentable_type (string) and commentable_id (ulid by default)
            $table->morphism('commentable');

            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');

            // Creates taggable_type and taggable_id columns
            // Uses default morph type from config
            $table->morphism('taggable');

            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('event');

            // Creates subject_type and subject_id (required)
            $table->morphism('subject');

            // Creates causer_type and causer_id (nullable)
            $table->nullableMorphism('causer');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('comments');
    }
};

/**
 * Example: Overriding the default morph type per-column.
 *
 * You can pass a MorphType enum value to override the default
 * for specific columns when needed.
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_relations', function (Blueprint $table): void {
            $table->id();

            // Force numeric morphs for legacy integer PKs
            $table->morphism('legacy_model', MorphType::Numeric);

            // Force UUID morphs for external system
            $table->nullableMorphism('external_ref', MorphType::UUID);

            // Use default (ULID) for new models
            $table->morphism('new_model');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_relations');
    }
};

/**
 * Example: Available MorphType options.
 */

// MorphType::ULID - Creates ulid column (26 chars, sortable, default)
// $table->morphism('subject', MorphType::ULID);

// MorphType::UUID - Creates uuid column (36 chars)
// $table->morphism('subject', MorphType::UUID);

// MorphType::Numeric - Creates unsignedBigInteger (for auto-increment PKs)
// $table->morphism('subject', MorphType::Numeric);

// MorphType::String - Creates string column (flexible, any key type)
// $table->morphism('subject', MorphType::String);

/**
 * Before Morphism (verbose):
 */
// match ($boundaryMorphType) {
//     MorphType::ULID => $table->nullableUlidMorphs('boundary'),
//     MorphType::UUID => $table->nullableUuidMorphs('boundary'),
//     MorphType::Numeric => $table->nullableNumericMorphs('boundary'),
//     MorphType::String => $table->nullableMorphs('boundary'),
// };

/**
 * After Morphism (clean):
 */
// $table->nullableMorphism('boundary');
// or with explicit type:
// $table->nullableMorphism('boundary', MorphType::UUID);
