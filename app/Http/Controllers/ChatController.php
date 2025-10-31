<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\ChatNewMessage;

class ChatController extends Controller
{
    public function start(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable','string','max:120'],
            'email' => ['nullable','email','max:255'],
        ]);
        $conv = Conversation::create([
            'token' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'name' => $data['name'] ?? (auth()->user()->name ?? null),
            'email' => $data['email'] ?? (auth()->user()->email ?? null),
            'last_message_at' => now(),
        ]);
        return response()->json(['ok' => true, 'token' => $conv->token]);
    }

    public function postMessage(Request $request, string $token)
    {
        $conv = Conversation::where('token', $token)->firstOrFail();
        if ($conv->closed_at) {
            return response()->json(['ok' => false, 'closed' => true, 'message' => 'This conversation is closed.'], 403);
        }

        $msg = null;
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => ['required','file','image','max:5120'], // 5MB
            ]);
            $file = $request->file('image');
            $path = $file->store('chat/'.date('Y/m/d'), 'public');
            $msg = ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender' => 'user',
                'body' => '[image]',
                'media_path' => $path,
                'media_mime' => $file->getClientMimeType(),
            ]);
            $payload = ['ok' => true, 'id' => $msg->id, 'at' => $msg->created_at?->toIso8601String(), 'image_url' => Storage::disk('public')->url($path)];
        } else {
            $data = $request->validate([
                'body' => ['required','string','max:2000'],
            ]);
            $msg = ChatMessage::create([
                'conversation_id' => $conv->id,
                'sender' => 'user',
                'body' => trim($data['body']),
            ]);
            $payload = ['ok' => true, 'id' => $msg->id, 'at' => $msg->created_at?->toIso8601String()];
        }

        $conv->update(['last_message_at' => now()]);

        try {
            $to = env('ADMIN_EMAIL');
            if ($to) { Mail::to($to)->queue(new ChatNewMessage($conv, $msg)); }
        } catch (\Throwable $e) { /* ignore */ }

        return response()->json($payload);
    }

    public function messages(Request $request, string $token)
    {
        $conv = Conversation::where('token', $token)->firstOrFail();
        $afterId = (int) $request->query('after_id', 0);
        $items = ChatMessage::where('conversation_id', $conv->id)
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
        return response()->json(['ok' => true, 'closed' => (bool) $conv->closed_at, 'messages' => $items]);
    }
}
