<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Post\PostStatusUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Liste des posts générés, avec filtre optionnel par statut.
     *
     * @group Posts
     * @authenticated
     *
     * @queryParam status string Filtre: draft, posted ou archived. Example: draft
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->whereHas('rawContent', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('rawContent', 'blueprint')
            ->withCount('versions')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->get();

        return PostResource::collection($posts);
    }

    /**
     * Détail d'un post avec son historique de versions.
     *
     * @group Posts
     * @authenticated
     *
     * @urlParam post integer required Example: 1
     */

    public function show(Request $request, Post $post)
    {
        $this->authorize('view', $post);

        $post->load('rawContent', 'blueprint', 'versions');

        return new PostResource($post);
    }

    /**
     * Mise à jour du statut d'un post (cycle de vie éditorial).
     *
     * @group Posts
     * @authenticated
     *
     * @urlParam post integer required Example: 1
     * @bodyParam status string required draft, posted ou archived. Example: posted
     */
    public function update(PostStatusUpdateRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }
}
