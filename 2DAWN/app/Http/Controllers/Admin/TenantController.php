<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::orderBy('name')->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $tenant = new Tenant();
        return view('admin.tenants.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Tenant::create($data);
        return redirect()->route('admin.tenants.index')->with('status','Tenant created');
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $this->validated($request, $tenant->id);
        $tenant->update($data);
        cache()->forget('tenant:'.$tenant->domain);
        return redirect()->route('admin.tenants.index')->with('status','Tenant updated');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        cache()->forget('tenant:'.$tenant->domain);
        return back()->with('status','Tenant deleted');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $domainRule = Rule::unique('tenants','domain');
        if ($ignoreId) $domainRule = $domainRule->ignore($ignoreId);
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'domain' => ['required','string','max:190',$domainRule],
            'support_email' => ['nullable','email','max:190'],
            'logo_url' => ['nullable','url','max:500'],
            'brand_color' => ['nullable','string','max:20'],
            'meta' => ['nullable','array'],
            'is_active' => ['sometimes','boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}