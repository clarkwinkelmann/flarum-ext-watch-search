<?php

namespace ClarkWinkelmann\WatchSearch\Controllers;

use Carbon\Carbon;
use ClarkWinkelmann\WatchSearch\SearchQuery;
use ClarkWinkelmann\WatchSearch\Serializers\SearchQuerySerializer;
use ClarkWinkelmann\WatchSearch\Validators\SearchQueryValidator;
use Flarum\Api\Controller\AbstractCreateController;
use Flarum\User\AssertPermissionTrait;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class StoreController extends AbstractCreateController
{
    use AssertPermissionTrait;

    public $serializer = SearchQuerySerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');

        $this->assertCan($actor, 'watch-search.use');

        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);

        /**
         * @var $validator SearchQueryValidator
         */
        $validator = app(SearchQueryValidator::class);

        $validator->assertValid($attributes);

        $searchQuery = new SearchQuery();
        $searchQuery->user()->associate($actor);
        $searchQuery->name = Arr::get($attributes, 'name');
        $searchQuery->query = Arr::get($attributes, 'query');
        $searchQuery->last_check = Carbon::now();
        $searchQuery->save();

        return $searchQuery;
    }
}
