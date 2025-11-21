<?php

namespace TrueFans\LaravelReactable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use TrueFans\LaravelReactable\Traits\HasComments;
use TrueFans\LaravelReactable\Traits\HasReactions;

class Post extends Model
{
    use HasComments;
    use HasReactions;

    protected $fillable = ['user_id', 'title', 'content', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
