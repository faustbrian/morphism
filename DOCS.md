## Table of Contents

1. Overview (`docs/README.md`)
2. Basic Usage (`docs/basic-usage.md`)
3. Config Based Setup (`docs/config-based-setup.md`)
4. Migrations (`docs/migrations.md`)
5. Strict Enforcement (`docs/strict-enforcement.md`)
6. Testing (`docs/testing.md`)
Morphism provides enhanced polymorphic relationship management for Laravel with strict type enforcement, automatic morphing, and cleaner configuration.

## Installation

```bash
composer require cline/morphism
```

## Basic Setup

### Service Provider

```php
// config/app.php
'providers' => [
    Cline\Morphism\MorphismServiceProvider::class,
],
```

### Configuration

```bash
php artisan vendor:publish --tag=morphism-config
```

```php
// config/morphism.php
return [
    'morphs' => [
        'commentable' => [
            App\Models\Post::class,
            App\Models\Video::class,
            App\Models\Article::class,
        ],
        'taggable' => [
            App\Models\Post::class,
            App\Models\Product::class,
        ],
    ],
];
```

## Basic Usage

```php
use App\Models\Comment;
use App\Models\Post;

// Create polymorphic relation
$post = Post::find(1);
$comment = new Comment(['body' => 'Great post!']);
$post->comments()->save($comment);

// Query polymorphic relation
$comments = Comment::where('commentable_type', Post::class)->get();
```

## Why Morphism?

### Problem: Default Laravel Morphs

```php
// Without Morphism, morph maps use full class names
// commentable_type = "App\Models\Post"

// This breaks if you rename/move classes
// Couples database to PHP namespaces
```

### Solution: Morphism

```php
// With Morphism, morph maps use clean aliases
// commentable_type = "post"

// Rename/move classes freely
// Database stays clean
// Type enforcement prevents invalid relations
```

## Next Steps

- [Basic Usage](./basic-usage.md) - Working with morphs
- [Config-Based Setup](./config-based-setup.md) - Configuration options
- [Strict Enforcement](./strict-enforcement.md) - Type safety
- [Migrations](./migrations.md) - Database setup
- [Testing](./testing.md) - Testing with Morpheus

Working with polymorphic relationships using Morphism.

## Defining Morphs

### In Configuration

```php
// config/morphism.php
return [
    'morphs' => [
        'commentable' => [
            App\Models\Post::class,
            App\Models\Video::class,
        ],
    ],
];
```

### In Models

```php
use Cline\Morphism\Traits\HasMorphs;

class Comment extends Model
{
    use HasMorphs;

    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    use HasMorphs;

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

## Creating Relations

```php
// From parent model
$post = Post::find(1);
$post->comments()->create([
    'body' => 'Great content!',
    'user_id' => auth()->id(),
]);

// From child model
$comment = Comment::create([
    'body' => 'Nice video!',
    'user_id' => auth()->id(),
    'commentable_type' => 'video',  // Uses alias, not class name
    'commentable_id' => $video->id,
]);

// Associate
$comment = new Comment(['body' => 'Hello']);
$comment->commentable()->associate($post);
$comment->save();
```

## Querying Relations

```php
// Get all comments for a post
$comments = $post->comments;

// Get the parent model
$parent = $comment->commentable;

// Query by type
$postComments = Comment::where('commentable_type', 'post')->get();

// With morphed type
$postComments = Comment::whereMorphedTo('commentable', Post::class)->get();
```

## Multiple Morph Types

```php
// config/morphism.php
return [
    'morphs' => [
        'commentable' => [
            App\Models\Post::class,
            App\Models\Video::class,
            App\Models\Article::class,
        ],
        'taggable' => [
            App\Models\Post::class,
            App\Models\Product::class,
            App\Models\Category::class,
        ],
        'likeable' => [
            App\Models\Post::class,
            App\Models\Comment::class,
            App\Models\Photo::class,
        ],
    ],
];
```

## Morph Map Aliases

```php
// Custom aliases
return [
    'morphs' => [
        'commentable' => [
            'post' => App\Models\Post::class,
            'video' => App\Models\Video::class,
            'article' => App\Models\Article::class,
        ],
    ],
];

// Database stores 'post', 'video', 'article' instead of full class names
```

## Eager Loading

```php
// Eager load morph relation
$comments = Comment::with('commentable')->get();

// Constrained eager load
$comments = Comment::with(['commentable' => function ($query) {
    $query->where('published', true);
}])->get();

// Morph with specific types
$comments = Comment::with([
    'commentable' => function (MorphTo $morphTo) {
        $morphTo->morphWith([
            Post::class => ['author'],
            Video::class => ['channel'],
        ]);
    },
])->get();
```

Configure polymorphic relationships centrally.

## Configuration File

```php
// config/morphism.php
return [
    /*
    |--------------------------------------------------------------------------
    | Morph Maps
    |--------------------------------------------------------------------------
    |
    | Define all polymorphic relationship types and their allowed models.
    |
    */
    'morphs' => [
        'commentable' => [
            'post' => App\Models\Post::class,
            'video' => App\Models\Video::class,
            'article' => App\Models\Article::class,
        ],

        'taggable' => [
            'post' => App\Models\Post::class,
            'product' => App\Models\Product::class,
        ],

        'imageable' => [
            'user' => App\Models\User::class,
            'product' => App\Models\Product::class,
            'category' => App\Models\Category::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, only models listed in morphs can be used in relations.
    |
    */
    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Automatically discover morph maps from model attributes.
    |
    */
    'auto_discover' => false,
];
```

## Publishing Configuration

```bash
php artisan vendor:publish --tag=morphism-config
```

## Environment-Based Config

```php
// config/morphism.php
return [
    'strict' => env('MORPHISM_STRICT', true),

    'morphs' => [
        'commentable' => array_filter([
            'post' => App\Models\Post::class,
            'video' => App\Models\Video::class,
            // Only in non-production
            'test' => app()->environment('local') ? App\Models\TestModel::class : null,
        ]),
    ],
];
```

## Dynamic Configuration

```php
use Cline\Morphism\Facades\Morphism;

// Add morphs at runtime
Morphism::register('commentable', [
    'article' => App\Models\Article::class,
]);

// Get all morphs for a type
$morphs = Morphism::getMorphs('commentable');

// Check if type is registered
Morphism::has('commentable'); // true

// Get alias for class
Morphism::getAlias(Post::class, 'commentable'); // 'post'

// Get class for alias
Morphism::getClass('post', 'commentable'); // App\Models\Post::class
```

## Model-Based Configuration

```php
use Cline\Morphism\Attributes\Morphable;

#[Morphable('commentable', alias: 'post')]
#[Morphable('taggable', alias: 'post')]
class Post extends Model
{
    // This model can be used in commentable and taggable relations
}

// Enable auto-discovery
// config/morphism.php
return [
    'auto_discover' => true,
    'discover_paths' => [
        app_path('Models'),
    ],
];
```

## Validation

```php
// Validate configuration on boot (development)
Morphism::validate();

// Check for issues
$issues = Morphism::diagnose();
foreach ($issues as $issue) {
    logger()->warning($issue);
}
```

Database migrations for polymorphic relationships.

## Basic Migration

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->foreignId('user_id')->constrained();

            // Polymorphic columns
            $table->morphs('commentable');
            // Creates: commentable_type (string), commentable_id (bigint)
            // Creates: index on both columns

            $table->timestamps();
        });
    }
};
```

## Nullable Morphs

```php
Schema::create('images', function (Blueprint $table) {
    $table->id();
    $table->string('path');

    // Nullable polymorphic relation
    $table->nullableMorphs('imageable');

    $table->timestamps();
});
```

## UUID Morphs

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('body');

    // For UUID primary keys
    $table->uuidMorphs('commentable');
    // Creates: commentable_type (string), commentable_id (uuid)

    $table->timestamps();
});

// Nullable UUID morphs
$table->nullableUuidMorphs('commentable');
```

## ULID Morphs

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('body');

    // For ULID primary keys
    $table->ulidMorphs('commentable');

    $table->timestamps();
});
```

## Custom Column Types

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('body');

    // Custom morph columns
    $table->string('commentable_type', 50); // Shorter type column
    $table->unsignedBigInteger('commentable_id');
    $table->index(['commentable_type', 'commentable_id']);

    $table->timestamps();
});
```

## Adding Morphs to Existing Table

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('existing_table', function (Blueprint $table) {
            $table->nullableMorphs('taggable');
        });
    }

    public function down(): void
    {
        Schema::table('existing_table', function (Blueprint $table) {
            $table->dropMorphs('taggable');
        });
    }
};
```

## Migrating Morph Types

```php
// Migration to convert class names to aliases
return new class extends Migration
{
    public function up(): void
    {
        // Update existing records to use aliases
        DB::table('comments')
            ->where('commentable_type', 'App\\Models\\Post')
            ->update(['commentable_type' => 'post']);

        DB::table('comments')
            ->where('commentable_type', 'App\\Models\\Video')
            ->update(['commentable_type' => 'video']);
    }
};
```

## Indexing Strategies

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->morphs('commentable');

    // Additional indexes for common queries
    $table->index(['commentable_type', 'created_at']);
    $table->index(['commentable_id', 'commentable_type']);
});
```

Type-safe polymorphic relationships with strict mode.

## Enabling Strict Mode

```php
// config/morphism.php
return [
    'strict' => true,

    'morphs' => [
        'commentable' => [
            'post' => App\Models\Post::class,
            'video' => App\Models\Video::class,
        ],
    ],
];
```

## What Strict Mode Does

### Prevents Invalid Types

```php
// With strict mode ON
$comment = new Comment();
$comment->commentable()->associate($product); // Throws exception!
// "Product is not a valid commentable type"

// Only configured types allowed
$comment->commentable()->associate($post); // Works
$comment->commentable()->associate($video); // Works
```

### Validates on Save

```php
// Even manual assignment is validated
$comment = new Comment();
$comment->commentable_type = 'product'; // Invalid
$comment->commentable_id = 1;
$comment->save(); // Throws MorphismException!
```

### Validates on Query

```php
// Invalid morph type in query
Comment::where('commentable_type', 'invalid')->get();
// Throws exception in strict mode
```

## Strict Mode Levels

```php
// config/morphism.php
return [
    // Full strict - validates everything
    'strict' => true,

    // Or granular control
    'strict' => [
        'save' => true,      // Validate on model save
        'query' => true,     // Validate in queries
        'associate' => true, // Validate on associate()
        'create' => true,    // Validate on create/make
    ],
];
```

## Exception Handling

```php
use Cline\Morphism\Exceptions\InvalidMorphTypeException;

try {
    $comment->commentable()->associate($invalidModel);
} catch (InvalidMorphTypeException $e) {
    // Handle invalid type
    $validTypes = $e->getValidTypes();
    $attemptedType = $e->getAttemptedType();
    $morphName = $e->getMorphName();
}
```

## Disabling Per-Operation

```php
use Cline\Morphism\Facades\Morphism;

// Temporarily disable strict mode
Morphism::withoutStrict(function () {
    $comment->commentable()->associate($anyModel);
});

// Or for specific morph
$comment->commentable()->withoutStrict()->associate($anyModel);
```

## Development vs Production

```php
// config/morphism.php
return [
    // Strict in development, permissive in production
    'strict' => env('APP_ENV') !== 'production',

    // Or always strict but log instead of throw in production
    'strict' => true,
    'strict_action' => env('APP_ENV') === 'production' ? 'log' : 'throw',
];
```

## Custom Validation

```php
use Cline\Morphism\Facades\Morphism;

Morphism::validateUsing('commentable', function ($model, $type) {
    // Custom validation logic
    if ($model instanceof Post && !$model->allow_comments) {
        return false;
    }
    return true;
});
```

Testing polymorphic relationships with the Morpheus helper.

## Basic Testing

```php
use Cline\Morphism\Testing\Morpheus;
use Tests\TestCase;

class CommentTest extends TestCase
{
    public function test_comment_can_belong_to_post(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'commentable_type' => 'post',
            'commentable_id' => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $comment->commentable);
        $this->assertTrue($comment->commentable->is($post));
    }
}
```

## Using Morpheus

```php
use Cline\Morphism\Testing\Morpheus;

class MorphismTest extends TestCase
{
    public function test_morph_map_is_configured(): void
    {
        Morpheus::assertMorphMapContains('commentable', [
            'post' => Post::class,
            'video' => Video::class,
        ]);
    }

    public function test_model_has_correct_morph_alias(): void
    {
        Morpheus::assertMorphAlias(Post::class, 'post');
        Morpheus::assertMorphAlias(Video::class, 'video');
    }
}
```

## Testing Strict Mode

```php
use Cline\Morphism\Exceptions\InvalidMorphTypeException;

class StrictModeTest extends TestCase
{
    public function test_invalid_morph_type_throws_exception(): void
    {
        $this->expectException(InvalidMorphTypeException::class);

        $comment = new Comment();
        $comment->commentable_type = 'invalid_type';
        $comment->commentable_id = 1;
        $comment->save();
    }

    public function test_valid_morph_type_saves(): void
    {
        $post = Post::factory()->create();

        $comment = Comment::factory()->make();
        $comment->commentable()->associate($post);
        $comment->save();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'commentable_type' => 'post',
            'commentable_id' => $post->id,
        ]);
    }
}
```

## Factory States

```php
// CommentFactory.php
class CommentFactory extends Factory
{
    public function forPost(Post $post = null): static
    {
        return $this->state(fn() => [
            'commentable_type' => 'post',
            'commentable_id' => $post?->id ?? Post::factory(),
        ]);
    }

    public function forVideo(Video $video = null): static
    {
        return $this->state(fn() => [
            'commentable_type' => 'video',
            'commentable_id' => $video?->id ?? Video::factory(),
        ]);
    }
}

// Usage
$comment = Comment::factory()->forPost()->create();
$comment = Comment::factory()->forVideo($video)->create();
```

## Testing Relationships

```php
class RelationshipTest extends TestCase
{
    public function test_post_has_many_comments(): void
    {
        $post = Post::factory()
            ->has(Comment::factory()->count(3), 'comments')
            ->create();

        $this->assertCount(3, $post->comments);
        $this->assertContainsOnlyInstancesOf(Comment::class, $post->comments);
    }

    public function test_comment_belongs_to_morphable(): void
    {
        $post = Post::factory()->create();
        $video = Video::factory()->create();

        $postComment = Comment::factory()->forPost($post)->create();
        $videoComment = Comment::factory()->forVideo($video)->create();

        $this->assertInstanceOf(Post::class, $postComment->commentable);
        $this->assertInstanceOf(Video::class, $videoComment->commentable);
    }
}
```

## Mocking Morphs

```php
use Cline\Morphism\Testing\Morpheus;

class MockedMorphTest extends TestCase
{
    public function test_with_mocked_morphs(): void
    {
        Morpheus::fake([
            'commentable' => [
                'mock' => MockModel::class,
            ],
        ]);

        // Test with mocked morph map
        $this->assertEquals(MockModel::class, Morpheus::getClass('mock', 'commentable'));
    }

    protected function tearDown(): void
    {
        Morpheus::restore();
        parent::tearDown();
    }
}
```
