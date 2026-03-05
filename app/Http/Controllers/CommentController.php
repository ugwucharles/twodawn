<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\LoggerService;

class CommentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        abort_unless($event->is_published, 404);

        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'email' => ['nullable','email','max:120'],
            'content' => ['required','string','max:2000'],
        ]);

        $comment = Comment::create([
            'event_id' => $event->id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'content' => $data['content'],
            'approved' => true,
        ]);

        LoggerService::logUserAction('Comment submitted', array_merge([
            'event_id' => $event->id,
            'event_title' => $event->title,
            'comment_id' => $comment->id,
            'commenter_name' => $data['name'],
            'commenter_email' => $data['email'] ?? null,
        ], LoggerService::getRequestContext($request)));

        return back()->with('status', 'Comment posted successfully.');
    }
}
