<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ai\Agents\GhostwriterAgent;
use App\Http\Requests\Chat\ChatRequest;
use App\Models\Post;

class ChatController extends Controller
{
    public function send(ChatRequest $request, Post $post)
    {
        $this->authorize('view', $post);

        $agent = new GhostwriterAgent($post);
        $user = $request->user();

        $conversationId = $request->validated('conversation__id');

        $response = $conversationId 
            ? $agent->continue($conversationId, as: $user)->prompt($request->validated('message'))
            : $agent->forUser($user)->prompt($request->validated('message'));

        $versionId = null;

        if ($request->boolean('save_as_version')) {
            $nextVersionNumber = $post->versions()->max('version_number') + 1;

            $version = $post->versions()->create([
                'content' => (string) $response,
                'version_number' => $nextVersionNumber,
            ]);

            $versionId = $version->id;
        }

        return response()->json([
            'reply' => (string) $response,
            'conversation_id' => $response->conversationId,
            'version_id' => $versionId,
        ]);
    }
}
