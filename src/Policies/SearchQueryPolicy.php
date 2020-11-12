<?php

namespace ClarkWinkelmann\WatchSearch\Policies;

use ClarkWinkelmann\WatchSearch\SearchQuery;
use Flarum\User\AbstractPolicy;
use Flarum\User\User;

class SearchQueryPolicy extends AbstractPolicy
{
    protected $model = SearchQuery::class;

    public function edit(User $actor, SearchQuery $query)
    {
        return $query->user_id === $actor->id && $actor->hasPermission('watch-search.use');
    }
}
