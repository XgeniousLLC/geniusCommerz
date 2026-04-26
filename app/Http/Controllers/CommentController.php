<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commentable_type' => 'required|in:blog',
            'commentable_id'   => 'required|integer',
            'parent_id'        => 'nullable|integer|exists:comments,id',
            'body'             => 'required|string|max:2000',
            'rating'           => 'nullable|integer|min:1|max:5',
        ]);

        $user = $request->user();

        $modelMap = ['blog' => Blog::class];
        $modelClass = $modelMap[$data['commentable_type']];
        $model = $modelClass::findOrFail($data['commentable_id']);

        $model->comments()->create([
            'user_id'    => $user->id,
            'parent_id'  => $data['parent_id'] ?? null,
            'name'       => $user->name,
            'email'      => $user->email,
            'body'       => $data['body'],
            'rating'     => $data['rating'] ?? null,
            'is_approved' => true,
        ]);

        return back()->with('comment_success', 'Your comment has been posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }
}
