<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ai\Agents\GhostwriterAgent;
use App\Http\Requests\Chat\ChatRequest;
use App\Models\Post;

class ChatController extends Controller
{
    /**
     * Envoie un message au Ghostwriter Agent pour un post donné.
     *
     * @group Chat
     * @authenticated
     *
     * @urlParam post integer required L'ID du post concerné. Example: 1
     * @bodyParam message string required Example: Donne-moi 3 variantes plus agressives pour le hook.
     * @bodyParam conversation_id string Optionnel, pour continuer une conversation existante.
     * @bodyParam save_as_version boolean Optionnel, sauvegarde la réponse comme nouvelle PostVersion.
     *
     * @response 200 {
     *   "reply": "Voici 3 variantes plus agressives...",
     *   "conversation_id": "abc123",
     *   "version_id": null
     * }
     */
    public function send(ChatRequest $request, Post $post)
    {
        $this->authorize('view', $post);

        $agent = new GhostwriterAgent($post);
        $user = $request->user();

        $conversationId = $request->validated('conversation_id');

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
