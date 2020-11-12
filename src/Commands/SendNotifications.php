<?php

namespace ClarkWinkelmann\WatchSearch\Commands;

use Carbon\Carbon;
use ClarkWinkelmann\WatchSearch\Notifications\NewDiscussionBlueprint;
use ClarkWinkelmann\WatchSearch\SearchQuery;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Event\Searching;
use Flarum\Discussion\Search\DiscussionSearch;
use Flarum\Discussion\Search\Gambit\AuthorGambit;
use Flarum\Discussion\Search\Gambit\CreatedGambit;
use Flarum\Discussion\Search\Gambit\FulltextGambit;
use Flarum\Discussion\Search\Gambit\HiddenGambit;
use Flarum\Discussion\Search\Gambit\UnreadGambit;
use Flarum\Event\ConfigureDiscussionGambits;
use Flarum\Notification\NotificationSyncer;
use Flarum\Search\GambitManager;
use Flarum\Search\SearchCriteria;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;

class SendNotifications extends Command
{
    protected $signature = 'clarkwinkelmann:watch-search:process';
    protected $description = 'Process query searches and send notifications';

    protected $discussions;
    protected $events;
    protected $notifications;

    public function __construct(DiscussionRepository $discussions, Dispatcher $events, NotificationSyncer $notifications)
    {
        parent::__construct();

        $this->discussions = $discussions;
        $this->events = $events;
        $this->notifications = $notifications;
    }

    public function handle()
    {
        $gambits = new GambitManager(app());

        $gambits->setFulltextGambit(FulltextGambit::class);
        $gambits->add(AuthorGambit::class);
        $gambits->add(CreatedGambit::class);
        $gambits->add(HiddenGambit::class);
        $gambits->add(UnreadGambit::class);

        $this->events->dispatch(
            new ConfigureDiscussionGambits($gambits)
        );

        $progress = $this->output->createProgressBar(SearchQuery::query()->count());

        $sentUserCount = 0;
        $sentNotificationCount = 0;

        SearchQuery::query()->with('user')->each(function (SearchQuery $searchQuery) use ($gambits, $progress, &$sentUserCount, &$sentNotificationCount) {
            $criteria = new SearchCriteria($searchQuery->user, $searchQuery->query);

            /**
             * @var $query Builder
             */
            $query = $this->discussions->query()->select('discussions.*')->whereVisibleTo($searchQuery->user);

            $search = new DiscussionSearch($query->getQuery(), $searchQuery->user);

            $gambits->apply($search, $criteria->query);

            $this->events->dispatch(new Searching($search, $criteria));

            if ($searchQuery->last_check) {
                $query->where('discussions.created_at', '>', $searchQuery->last_check);
            }

            // Don't notify user of their own discussions
            // TODO: != <int> doesn't seem to select null values...
            $query->where('discussions.user_id', '!=', $searchQuery->user->id);

            // We don't use ->each() on the builder, because this could use multiple batch queries
            // Which we don't want since it would mess up the results if a new discussion is created during that time
            $discussions = $query->get();

            // We do this between the request and the loop, because if no queue is set up, sending notifications
            // can take some time, and we don't want that influencing the time of last scan
            $searchQuery->last_check = Carbon::now();
            $searchQuery->save();

            if ($discussions->count()) {
                $sentUserCount++;
                $sentNotificationCount += $discussions->count();

                $discussions->each(function (Discussion $discussion) use ($searchQuery) {
                    $this->notifications->sync(
                        new NewDiscussionBlueprint($discussion, $searchQuery),
                        [$searchQuery->user]
                    );
                });
            }

            $progress->advance();
        });

        $progress->finish();
        $this->line('');

        $this->info("Sent $sentNotificationCount notifications to $sentUserCount users");
    }
}
