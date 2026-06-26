<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use App\Models\Post;
use Stringable;

class GetPostHistory implements Tool
{
    public function __construct(private Post $post) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the previous edited versions of the current post, in chronological order.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $versions = $this->post->versions()->orderBy('version_number')->get();

        if ($versions->isEmpty()) {
            return 'No previous versions exist for this post yet.';
        }

        return $versions
            ->map(fn ($v) => "Version {$v->version_number}: {$v->content}")
            ->implode("\n\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            // 'value' => $schema->string()->required(),
        ];
    }
}
