<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('organizer.settings.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'instagram_handle' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'twitter_handle' => 'nullable|string|max:255',
        ]);

        Auth::user()->update([
            'instagram_handle' => $request->instagram_handle,
            'whatsapp_number' => $request->whatsapp_number,
            'twitter_handle' => $request->twitter_handle,
        ]);

        return back()->with('status', 'Settings updated successfully!');
    }
}
