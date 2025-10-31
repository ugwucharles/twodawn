@php($t = $tenant)
<div class="space-y-4">
  <div>
    <label class="block text-sm text-zinc-300">Name</label>
    <input name="name" value="{{ old('name', $t->name) }}" required class="mt-1 block w-full rounded bg-black/30 border border-white/10 px-3 py-2" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
  </div>
  <div>
    <label class="block text-sm text-zinc-300">Domain</label>
    <input name="domain" value="{{ old('domain', $t->domain) }}" required class="mt-1 block w-full rounded bg-black/30 border border-white/10 px-3 py-2" placeholder="tenant.example.com" />
    <x-input-error :messages="$errors->get('domain')" class="mt-2" />
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm text-zinc-300">Support email</label>
      <input name="support_email" type="email" value="{{ old('support_email', $t->support_email) }}" class="mt-1 block w-full rounded bg-black/30 border border-white/10 px-3 py-2" />
      <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
    </div>
    <div>
      <label class="block text-sm text-zinc-300">Brand color</label>
      <input name="brand_color" value="{{ old('brand_color', $t->brand_color) }}" class="mt-1 block w-full rounded bg-black/30 border border-white/10 px-3 py-2" placeholder="#111827" />
      <x-input-error :messages="$errors->get('brand_color')" class="mt-2" />
    </div>
  </div>
  <div>
    <label class="block text-sm text-zinc-300">Logo URL</label>
    <input name="logo_url" value="{{ old('logo_url', $t->logo_url) }}" class="mt-1 block w-full rounded bg-black/30 border border-white/10 px-3 py-2" placeholder="https://.../logo.png" />
    <x-input-error :messages="$errors->get('logo_url')" class="mt-2" />
  </div>
  <div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-white/20 text-indigo-500" @checked(old('is_active', $t->is_active)) />
    <label for="is_active" class="text-sm text-zinc-300">Active</label>
  </div>
</div>