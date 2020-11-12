<?php

namespace ClarkWinkelmann\WatchSearch;

use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Event\ConfigureNotificationTypes;
use Flarum\Extend;
use Flarum\Foundation\Application;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Extend\Routes('api'))
        ->post('/watch-search-queries', 'watch-search-queries.store', Controllers\StoreController::class)
        ->patch('/watch-search-queries/{id:[a-f0-9-]+}', 'watch-search-queries.update', Controllers\UpdateController::class)
        ->delete('/watch-search-queries/{id:[a-f0-9-]+}', 'watch-search-queries.delete', Controllers\DeleteController::class),

    (new Extend\Console())
        ->command(Commands\SendNotifications::class),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    function (Application $app, Dispatcher $events, Factory $views) {
        $app->register(Providers\ForumAttributes::class);

        $events->subscribe(Policies\SearchQueryPolicy::class);

        $events->listen(ConfigureNotificationTypes::class, function (ConfigureNotificationTypes $event) {
            $event->add(Notifications\NewDiscussionBlueprint::class, DiscussionSerializer::class, ['alert', 'email']);
        });

        $views->addNamespace('clarkwinkelmann-watch-search', __DIR__ . '/resources/views');
    },
];
