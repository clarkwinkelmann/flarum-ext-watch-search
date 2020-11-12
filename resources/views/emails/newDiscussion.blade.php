Hey {!! $user->display_name !!}!

{!! optional($blueprint->discussion->user)->display_name !!} made a discussion in a tag you're following: {!! $blueprint->discussion->title !!}

To view the new discussion, check out the following link:
{!! app()->url() !!}/d/{!! $blueprint->discussion->id !!}

---

@if ($blueprint->discussion->firstPost instanceof \Flarum\Post\CommentPost)
{!! $blueprint->discussion->firstPost->content !!}
@endif
