<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'content',
        'version_number',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
