<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Ai\Responses\AgentResponse;
use App\Ai\Agents\PostGeneratorAgent;
use App\Models\Blueprint;
use App\Models\Post;
use App\Models\RawContent;
use RuntimeException;
use Throwable;

class GeneratePostJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $rawContentId,
        public int $blueprintId,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rawContent = RawContent::findOrFail($this->rawContentId);
        $blueprint = Blueprint::findOrFail($this->blueprintId);

        $response = (new PostGeneratorAgent($rawContent, $blueprint))
            ->prompt($rawContent->content);

        $this->assertContractIsValid($response);

        Post::create([
            'raw_content_id' => $rawContent->id,
            'blueprint_id' => $blueprint->id,
            'hook_propose' => $response['hook_propose'],
            'body_points' => $this->normalizeArrayField($response['body_points']),
            'technical_readability_score' => $response['technicalreadabilityscore'],
            'suggested_hashtags' => $this->normalizeArrayField($response['suggested_hashtags']),
            'tone_compliance_justification' => $response['tonecompliancejustification'],
            'status' => 'draft',
            'generated_at' => now(),
        ]);
    }

    /**
     * Le SDK IA retourne parfois ce champ comme string JSON plutôt que
     * comme tableau PHP natif, selon le provider. On normalise ici pour
     * que le cast `array` d'Eloquent ne double-encode pas la valeur.
     */
    private function normalizeArrayField(mixed $value): array
    {
        return is_string($value) ? json_decode($value, true) : $value;
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
            if (!isset($response[$key])) {
                throw new RuntimeException("AI response missing required key: {$key}");
            }
        }
    }

    public function failed(Throwable $exception): void 
    {
        logger()->error('GeneratePostJob failed', [
            'raw_content_id' => $this->rawContentId,
            'error' => $exception->getMessage(),
        ]);
    }
}
