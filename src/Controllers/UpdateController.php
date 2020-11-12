<?php

namespace ClarkWinkelmann\WatchSearch\Controllers;

use Carbon\Carbon;
use ClarkWinkelmann\WatchSearch\SearchQuery;
use ClarkWinkelmann\WatchSearch\Serializers\SearchQuerySerializer;
use Flarum\Api\Controller\AbstractShowController;
use Flarum\User\AssertPermissionTrait;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class UpdateController extends AbstractShowController
{
    use AssertPermissionTrait;

    public $serializer = SearchQuerySerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $id = Arr::get($request->getQueryParams(), 'id');

        $actor = $request->getAttribute('actor');

        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);

        /**
         * @var $searchQuery SearchQuery
         */
        $searchQuery = SearchQuery::query()->findOrFail($id);

        $this->assertCan($actor, 'edit', $searchQuery);

        if (Arr::exists($attributes, 'name')) {
            $searchQuery->name = Arr::get($attributes, 'name');
        }

        if (Arr::exists($attributes, 'query')) {
            $searchQuery->query = Arr::get($attributes, 'query');
            $searchQuery->last_check = Carbon::now();
        }

        $searchQuery->save();

        return $searchQuery;
    }
}
