@extends('layouts.public')

@section('title', 'Page not found')
@section('meta_description', 'The page you are looking for could not be found.')
@section('robots', 'noindex, follow')

@section('content')
<section class="py-20 text-center">
  <h1 class="text-4xl font-extrabold mb-4">404 — Not Found</h1>
  <p class="text-zinc-300 mb-8">We couldn't find that page. It may have moved or no longer exists.</p>
  <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-white text-black rounded-xl font-semibold">Go home</a>
</section>
@endsection
