@extends('layouts.public')

@section('title', 'Something went wrong')
@section('meta_description', 'An unexpected error occurred.')
@section('robots', 'noindex, follow')

@section('content')
<section class="py-20 text-center">
  <h1 class="text-4xl font-extrabold mb-4">500 — Server Error</h1>
  <p class="text-zinc-300">We hit a snag. Please try again in a moment.</p>
</section>
@endsection
