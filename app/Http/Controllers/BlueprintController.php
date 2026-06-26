<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Blueprint\BlueprintStoreRequest;
use App\Http\Resources\BlueprintResource;
use App\Models\Blueprint;
use App\Http\Requests\Blueprint\BlueprintUpdateRequest;

class BlueprintController extends Controller
{
    /**
     * Liste des Blueprints du créateur, avec compteur de posts.
     *
     * @group Blueprints
     * @authenticated
     *
     * @response 200 [
     *   {"id": 1, "name": "Tech Community Aggressive", "tone": "professionnel mais décontracté", "max_hashtags": 1, "max_characters": 280, "additional_rules": "Pas de buzzwords corporate", "posts_count": 3, "created_at": "2026-06-23T10:00:00+00:00"}
     * ]
     */
    public function index(Request $request)
    {
        $blueprints = Blueprint::query()
            ->where('user_id', $request->user()->id)
            ->withCount('posts')
            ->latest()
            ->get();
        
        return BlueprintResource::collection($blueprints);
    }   

    /**
     * Création d'un nouveau Blueprint.
     *
     * @group Blueprints
     * @authenticated
     *
     * @bodyParam name string required Example: Tech Community Aggressive
     * @bodyParam tone string required Example: professionnel mais décontracté
     * @bodyParam max_hashtags integer required Entre 0 et 10. Example: 1
     * @bodyParam max_characters integer required Entre 1 et 280. Example: 280
     * @bodyParam additional_rules string Optionnel. Example: Pas de buzzwords corporate
     *
     * @response 201 {"id": 1, "name": "Tech Community Aggressive", "tone": "professionnel mais décontracté", "max_hashtags": 1, "max_characters": 280, "additional_rules": "Pas de buzzwords corporate", "created_at": "2026-06-23T10:00:00+00:00"}
     */
    public function store(BlueprintStoreRequest $request)
    {
        $blueprint = $request->user()->blueprints()->create($request->validated());

        return (new BlueprintResource($blueprint))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Détail d'un Blueprint (uniquement s'il appartient au créateur).
     *
     * @group Blueprints
     * @authenticated
     *
     * @urlParam blueprint integer required L'ID du Blueprint. Example: 1
     *
     * @response 403 {"message": "Ce blueprint ne vous appartient pas."}
     */
    public function show(Request $request, Blueprint $blueprint)
    {
        $this->authorize('view', $blueprint);

        $blueprint->loadCount('posts');

        return new BlueprintResource($blueprint);
    }

    /**
     * Mise à jour partielle d'un Blueprint.
     *
     * @group Blueprints
     * @authenticated
     *
     * @urlParam blueprint integer required Example: 1
     * @bodyParam tone string Optionnel, mise à jour partielle. Example: ironique et technique
     *
     * @response 200 {"id": 1, "name": "Tech Community Aggressive", "tone": "ironique et technique", "max_hashtags": 1, "max_characters": 280, "additional_rules": "Pas de buzzwords corporate", "created_at": "2026-06-23T10:00:00+00:00"}
     */
    public function update(BlueprintUpdateRequest $request, Blueprint $blueprint)
    {
        $this->authorize('view', $blueprint);

        $blueprint->update($request->validated());

        return new BlueprintResource($blueprint);
    }

    /**
     * Suppression d'un Blueprint.
     *
     * @group Blueprints
     * @authenticated
     *
     * @urlParam blueprint integer required Example: 1
     *
     * @response 204
     */
    public function destroy(Request $request, Blueprint $blueprint)
    {
        $this->authorize('view', $blueprint);

        $blueprint->delete();

        return response()->json(null, 204);
    }
}
