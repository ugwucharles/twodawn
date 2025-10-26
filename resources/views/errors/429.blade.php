@extends('layouts.public')

@section('title', 'Too many requests')
@section('meta_description', 'You are rate limited; please try again soon.')
@section('robots', 'noindex, follow')

@section('content')
<section class="py-20 text-center">
  <h1 class="text-4xl font-extrabold mb-4">429 — Too Many Requests</h1>
  <p class="text-zinc-300">Please wait a minute and try again.</p>
</section>
@endsection
