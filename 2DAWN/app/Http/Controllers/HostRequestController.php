<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\HostRequest;

class HostRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'event_title' => ['required','string','max:255'],
            'event_date' => ['nullable','date'],
            'venue' => ['nullable','string','max:255'],
            'expected_attendees' => ['nullable','integer','min:1'],
            'budget' => ['nullable','numeric','min:0'],
            'message' => ['nullable','string','max:5000'],
        ]);

        $payload = $data;
        $payload['budget_kobo'] = isset($data['budget']) ? (int) round($data['budget'] * 100) : null;
        unset($payload['budget']);

        HostRequest::create($payload);

        return back()->with('status', 'Thanks! Your hosting request has been submitted. We will reach out shortly.');
    }
}
