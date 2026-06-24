<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Blueprint\BluePrintStoreRequest;
use App\Http\Resources\BlueprintResource;
use App\Models\Blueprint;

class BlueprintController extends Controller
{
    public function index(Request $request)
    {
        $blueprints = Blueprint::query()
            ->where('user_id', $request->user()->id)
            ->withCount('posts')
            ->latest()
            ->get();
        
        return BlueprintResource::collection($blueprints);
    }

    public function store(BlueprintStoreRequest $request)
    {
        $blueprint = $request->user()->blueprints()->create($request->validated());

        return (new BlueprintResource($blueprint))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Blueprint $blueprint)
    {
        abort_if($blueprint->user_id !== $request->user()->id, 403, 'Ce blueprint ne vous appartient pas.');

        $blueprint->loadCount('posts');

        return new blueprintResource($blueprint);
    }
}
