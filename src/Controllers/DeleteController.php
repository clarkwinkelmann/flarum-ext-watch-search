<?php

namespace ClarkWinkelmann\WatchSearch\Controllers;

use ClarkWinkelmann\WatchSearch\SearchQuery;
use Flarum\Api\Controller\AbstractDeleteController;
use Flarum\User\AssertPermissionTrait;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class DeleteController extends AbstractDeleteController
{
    use AssertPermissionTrait;

    protected function delete(ServerRequestInterface $request)
    {
        $id = Arr::get($request->getQueryParams(), 'id');

        $searchQuery = SearchQuery::query()->findOrFail($id);

        $this->assertCan($request->getAttribute('actor'), 'edit', $searchQuery);

        $searchQuery->delete();
    }
}
