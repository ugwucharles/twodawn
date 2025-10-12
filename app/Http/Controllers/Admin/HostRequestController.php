<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostRequest;
use Illuminate\View\View;

class HostRequestController extends Controller
{
    public function index(): View
    {
        $requests = HostRequest::query()->latest()->paginate(15);
        return view('admin.host_requests.index', compact('requests'));
    }

    public function show(HostRequest $host_request): View
    {
        return view('admin.host_requests.show', ['req' => $host_request]);
    }

    public function update(HostRequest $host_request): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'status' => ['required','in:new,reviewing,approved,rejected,closed'],
        ]);
        $host_request->update($data);
        return back()->with('status', 'Status updated.');
    }
}
