<?php

namespace ClarkWinkelmann\WatchSearch;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\User\User;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $query
 * @property Carbon $last_check
 * @property Carbon $created_at
 *
 * @property User $user
 */
class SearchQuery extends AbstractModel
{
    protected $table = 'watch_search_queries';

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
