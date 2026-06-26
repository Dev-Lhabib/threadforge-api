<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Post\PostStatusUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
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
    public function show(Request $request, Post $post)
    {
        $this->authorize('view', $post);

        $post->load('rawContent', 'blueprint', 'versions');

        return new PostResource($post);
    }

    public function update(PostStatusUpdateRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }
}
