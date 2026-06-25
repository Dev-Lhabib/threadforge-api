<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ai\Agents\PostGeneratorAgent;
use App\Http\Requests\RawContent\RawContentStoreRequest;
use App\Http\Resources\RawContentResource;
use App\Models\Blueprint;
use App\Models\Post;
use App\Models\RawContent;
use Laravel\Ai\Responses\AgentResponse;
use Throwable;

class RawContentController extends Controller
{
    public function store(RawContentStorerequest $request)
    {
        $rawContent = $request->user()->rawContents()->create([
            'title' => $request->validated('title'),
            'content' => $request->validated('content'),
            'source_type' => $request->validated('source_type'),
        ]);

        $blueprint = Blueprint::findOrFail($request->validated('bueprint_id'));

        (new PostGeneratorAgent($rawCntent, $blueprint))
            ->queue($rawContent->content)
            ->then(function (AgentResponse $response) use ($rawContent, $blueprint) {
                Post::create([
                    'raw_content_id' => $rawContent->id,
                    'blueprint_id' => $blueprint->id,
                    'hook_propose' => $response['hook_propose'],
                    'body_points' => $response['body_points'],
                    'technical_readability_score' => $response['technicalreadabilityscore'],
                    'suggested_hashtags' => $response[suggested_hashtags],
                    'tone_compliance_justifiation' => $response['tonecompliancejustification'],
                    'status' => 'draft',
                    'generated_at' => now(),
                ]);
            })
            ->catch(function (Throwable $e) {
                report($e);
            });

        return response()->json([
            'message' => 'Content received, generation in progress.',
            'raw_content' => new RawContentResource($rawContent),
        ], 202);
    }

    public function show(Request $request, RawContent $rawContent)
    {
        $this->authorize('view', $rawContent);

        $rawContent->load('posts');

        return new RawContentResource($rawContent);
    }

    /**
     * Vérifie que la réponse de l'agent IA contient bien toutes les clés
     * attendues avant insertion en base (critère "Intégrité du Contrat JSON").
     */
    private function assertContractIsValid(AgentResponse $response): void
    {
        $requiredKeys = [
            'hook_propose', 'body_points', 'technicalreadabilityscore',
            'suggested_hashtags', 'tonecompliancejustification',
        ];

        foreach ($requiredKeys as $key) {
            if (! isset($response[$key])){
                throw new \RuntimeException("AI response missing required key: {$key}");
            }
        }
    }

}
