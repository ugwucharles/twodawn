@extends('layouts.public')

@section('title', 'Service unavailable')
@section('meta_description', 'We are performing maintenance. Please check back shortly.')
@section('robots', 'noindex, follow')

@section('content')
<section class="py-20 text-center">
  <h1 class="text-4xl font-extrabold mb-4">503 — Service Unavailable</h1>
  <p class="text-zinc-300">We're doing a quick update. Back soon.</p>
</section>
@endsection
