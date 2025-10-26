<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\HostToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostTokenController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'label' => ['nullable','string','max:100'],
        ]);
        $expires = optional($event->ends_at)->copy() ?: now();
        $expires = $expires->addDay();
        $token = HostToken::create([
            'event_id' => $event->id,
            'token' => 'H_'.Str::random(24),
            'label' => $data['label'] ?? null,
            'active' => true,
            'expires_at' => $expires,
        ]);
        return back()->with('status', 'Host link created: '.url('/h/'.$token->token));
    }

    public function toggle(HostToken $hostToken)
    {
        $hostToken->update(['active' => ! $hostToken->active]);
        return back()->with('status', 'Host link '.($hostToken->active ? 'activated' : 'deactivated'));
    }

    public function destroy(HostToken $hostToken)
    {
        $hostToken->delete();
        return back()->with('status', 'Host link revoked');
    }
}
