<?php

namespace ClarkWinkelmann\WatchSearch\Providers;

use ClarkWinkelmann\WatchSearch\SearchQuery;
use ClarkWinkelmann\WatchSearch\Serializers\SearchQuerySerializer;
use Flarum\Api\Controller\ShowForumController;
use Flarum\Api\Event\Serializing;
use Flarum\Api\Event\WillGetData;
use Flarum\Api\Event\WillSerializeData;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Event\GetApiRelationship;
use Flarum\Foundation\AbstractServiceProvider;

class ForumAttributes extends AbstractServiceProvider
{
    public function register()
    {
        $this->app['events']->listen(WillGetData::class, [$this, 'includes']);
        $this->app['events']->listen(GetApiRelationship::class, [$this, 'relationship']);
        $this->app['events']->listen(WillSerializeData::class, [$this, 'willSerialize']);
        $this->app['events']->listen(Serializing::class, [$this, 'serializing']);
    }

    public function includes(WillGetData $event)
    {
        if ($event->isController(ShowForumController::class)) {
            $event->addInclude(['watchSearchQueries']);
        }
    }

    public function relationship(GetApiRelationship $event)
    {
        if ($event->isRelationship(ForumSerializer::class, 'watchSearchQueries')) {
            return $event->serializer->hasMany($event->model, SearchQuerySerializer::class, 'watchSearchQueries');
        }
    }

    public function willSerialize(WillSerializeData $event)
    {
        if ($event->isController(ShowForumController::class)) {
            $event->data['watchSearchQueries'] = SearchQuery::query()->where('user_id', $event->actor->id)->get();
        }
    }

    public function serializing(Serializing $event)
    {
        if ($event->isSerializer(ForumSerializer::class)) {
            $event->attributes['canWatchSearch'] = $event->actor->hasPermission('watch-search.use');
        }
    }
}
