<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatAdminController extends Controller
{
    public function index()
    {
        $conversations = Conversation::query()
            ->orderByDesc('last_message_at')
            ->paginate(20);
        return view('admin.chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $messages = $conversation->messages()->orderBy('id')->get();
        return view('admin.chat.show', compact('conversation','messages'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $data = $request->validate([
            'body' => ['required','string','max:2000'],
        ]);
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => 'admin',
            'body' => trim($data['body']),
        ]);
        $conversation->update(['last_message_at' => now()]);
        return back();
    }

    public function messages(Conversation $conversation)
    {
        $afterId = (int) request()->query('after_id', 0);
        $items = $conversation->messages()
            ->when($afterId > 0, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'sender' => $m->sender,
                    'body' => $m->body,
                    'created_at' => $m->created_at?->toIso8601String(),
                    'media_url' => $m->media_path ? Storage::disk('public')->url($m->media_path) : null,
                ];
            });
        return response()->json(['ok'=>true,'messages'=>$items]);
    }

    public function close(Conversation $conversation)
    {
        $conversation->update(['closed_at' => now()]);
        return back();
    }

    public function reopen(Conversation $conversation)
    {
        $conversation->update(['closed_at' => null]);
        return back();
    }
}
