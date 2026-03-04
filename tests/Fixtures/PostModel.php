<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Test fixture model using 'uuid' as primary key.
 *
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class PostModel extends Model
{
    use HasFactory;

    #[Override()]
    public $incrementing = false;

    #[Override()]
    protected $table = 'posts';

    #[Override()]
    protected $primaryKey = 'uuid';

    #[Override()]
    protected $keyType = 'string';
}
