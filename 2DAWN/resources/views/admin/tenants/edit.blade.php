@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-6">
  <div class="mt-28 bg-white/5 ring-1 ring-white/10 rounded-2xl p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Tenant</h1>
    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-6">
      @csrf
      @method('PATCH')
      @include('admin.tenants._form')
      <div class="pt-4 flex items-center justify-end gap-3">
        <a href="{{ route('admin.tenants.index') }}" class="px-4 py-2 rounded-full bg-white/5 ring-1 ring-white/10">Cancel</a>
        <button class="px-5 py-2 rounded-full bg-white text-black font-semibold">Save</button>
      </div>
    </form>
  </div>
</div>
@endsection