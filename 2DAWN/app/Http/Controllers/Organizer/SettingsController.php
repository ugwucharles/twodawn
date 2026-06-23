<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        
        if (request()->expectsJson()) {
            return response()->json([
                'settings' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'instagram_handle' => $user->instagram_handle,
                    'whatsapp_number' => $user->whatsapp_number,
                    'twitter_handle' => $user->twitter_handle,
                    'profile_picture' => $user->profile_picture,
                ]
            ]);
        }
        
        return view('organizer.settings.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'instagram_handle' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'twitter_handle' => 'nullable|string|max:255',
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'instagram_handle' => $request->instagram_handle,
            'whatsapp_number' => $request->whatsapp_number,
            'twitter_handle' => $request->twitter_handle,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Settings updated successfully!']);
        }

        return back()->with('status', 'Settings updated successfully!');
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                try {
                    if (str_starts_with($user->profile_picture, 'http')) {
                        // Cloudinary URL - skip deletion
                    } else {
                        $oldPath = str_replace('/storage/', '', $user->profile_picture);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore deletion errors
                }
            }

            $file = $request->file('profile_picture');
            $path = $file->store('profile-pictures', 'public');
            
            $user->update(['profile_picture' => '/storage/' . $path]);

            return response()->json([
                'profile_picture' => url('/storage/' . $path),
                'message' => 'Profile picture updated successfully!'
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
}
