<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use App\Models\Post;
use App\Ai\Tools\GetCampaignRules;
use App\Ai\Tools\GetPostHistory;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Enums\Lab;
use Stringable;

#[Provider(Lab::Groq)]
#[Model('llama-3.3-70b-versatile')]
class GhostwriterAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(public Post $post) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a ghostwriter assistant helping a tech creator refine a generated social media post. '
             . 'Always use your tools to fetch the real campaign rules or post history instead of guessing them.';
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
        return [
            new GetCampaignRules($this->post),
            new GetPostHistory($this->post),
        ];
    }
}
