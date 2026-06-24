<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlueprintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tone' => $this->tone,
            'max_hashtags' => $this->max_hashtags,
            'max_characters' => $this->max_characters,
            'additional_rules' => $this->additional_rules,
            'posts_count' => $this->whenCounted('posts'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
