<?php

namespace ClarkWinkelmann\WatchSearch\Notifications;

use ClarkWinkelmann\WatchSearch\SearchQuery;
use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;

class NewDiscussionBlueprint implements BlueprintInterface, MailableInterface
{
    public $discussion;
    public $searchQuery;

    public function __construct(Discussion $discussion, SearchQuery $searchQuery)
    {
        $this->discussion = $discussion;
        $this->searchQuery = $searchQuery;
    }

    public function getFromUser()
    {
        return $this->discussion->user;
    }

    public function getSubject()
    {
        return $this->discussion;
    }

    public function getData()
    {
        return [];
    }

    public function getEmailView()
    {
        return ['text' => 'clarkwinkelmann-watch-search::emails.newDiscussion'];
    }

    public function getEmailSubject()
    {
        return app('translator')->trans('clarkwinkelmann-watch-search.email.newDiscussionInSearch', [
            '{title}' => $this->discussion->title,
            '{query}' => $this->searchQuery->name,
        ]);
    }

    public static function getType()
    {
        return 'newDiscussionInSearch';
    }

    public static function getSubjectModel()
    {
        return Discussion::class;
    }
}
