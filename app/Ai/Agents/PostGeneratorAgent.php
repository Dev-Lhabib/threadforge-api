<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;
use App\Models\Blueprint;
use App\Models\RawContent;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Enums\Lab;

#[Provider(Lab::Groq)]
#[Model('meta-llama/llama-4-scout-17b-16e-instruct')]
class PostGeneratorAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(
        public RawContent $rawContent,
        public Blueprint $blueprint,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return sprintf(
            "You are a tech content repurposing assistant.\nTone: %s\nMax hashtags: %d\nMax characters: %d\nAdditional rules: %s",
            $this->blueprint->tone,
            $this->blueprint->max_hashtags,
            $this->blueprint->max_characters,
            $this->blueprint->additional_rules ?? 'none',
        );
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'hook_propose' => $schema->string()->required(),
            'body_points' => $schema->array()->items($schema->string())->required(),
            'technicalreadabilityscore' => $schema->integer()->min(0)->max(100)->required(),
            'suggested_hashtags' => $schema->array()->items($schema->string())->required(),
            'tonecompliancejustification' => $schema->string()->required(),
        ];
    }
}
