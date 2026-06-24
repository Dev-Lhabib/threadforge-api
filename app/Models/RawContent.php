<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'source_type',
    ];

    public function user():BelongTo
    {
        return $this->belongTo(User::class);
    }

    public function poste(): HasMany
    {
        return $this->hasMany(Poste::class);
    }
}
