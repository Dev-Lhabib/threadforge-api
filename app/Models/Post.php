<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_content_id',
        'blueprint_id',
        'hook_propose',
        'body_points',
        'technical_readability_score',
        'suggested_hashtags',
        'tone_compliance_justification',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'body_points' => 'array',
        'suggested_hashtags' => 'array',
        'generated_at' => 'datetime',
    ];

    public function rawContent(): BelongsTo
    {
        return $this->belongsTo(RawContent::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PostVersion::class);
    }
}
