<?php

namespace ClarkWinkelmann\WatchSearch\Validators;

use Flarum\Foundation\AbstractValidator;

class SearchQueryValidator extends AbstractValidator
{
    protected $rules = [
        'name' => 'required|string|min:1|max:255',
        'query' => 'required|string|min:1|max:255',
    ];
}
