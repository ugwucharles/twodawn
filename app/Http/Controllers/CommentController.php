<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;

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

        Comment::create([
            'event_id' => $event->id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'content' => $data['content'],
            'approved' => false,
        ]);

        return back()->with('status', 'Comment submitted and awaiting approval.');
    }
}
