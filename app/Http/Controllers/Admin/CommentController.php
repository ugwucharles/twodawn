<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $approved = $status === 'approved' ? 1 : 0;

        $comments = Comment::with('event')
            ->where('approved', $approved)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'status'));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['approved' => true]);
        return back()->with('status', 'Comment approved');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('status', 'Comment deleted');
    }
}
