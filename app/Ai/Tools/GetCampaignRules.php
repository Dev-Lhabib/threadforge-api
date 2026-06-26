<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use App\Models\Post;
use Stringable;

class GetCampaignRules implements Tool
{
    public function __construct(private Post $post) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve the real style/campaign rules (Blueprint) applied to the current post. Always call this tool instead of guessing or inventing the rules.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $blueprint = $this->blueprint;

        return sprintf(
            "Tone: %s\nMax hashtags: %d\nMax characters: %d\nAdditional rules: %s",
            $blueprint->tone,
            $blueprint->max_hashtags,
            $blueprint->max_characters,
            $blueprint->additional_rules ?? 'none',
        );
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
