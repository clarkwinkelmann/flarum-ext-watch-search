<?php


namespace ClarkWinkelmann\WatchSearch\Serializers;

use Flarum\Api\Serializer\AbstractSerializer;

class SearchQuerySerializer extends AbstractSerializer
{
    protected $type = 'watch-search-queries';

    protected function getDefaultAttributes($model)
    {
        return [
            'name' => $model->name,
            'query' => $model->query,
        ];
    }
}
