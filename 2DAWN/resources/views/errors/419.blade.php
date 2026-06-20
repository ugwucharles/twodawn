@extends('layouts.public')

@section('title', 'Page expired')
@section('meta_description', 'Please refresh and try again.')
@section('robots', 'noindex, follow')

@section('content')
<section class="py-20 text-center">
  <h1 class="text-4xl font-extrabold mb-4">419 — Page Expired</h1>
  <p class="text-zinc-300">Your session expired or the form was open too long. Please go back and submit again.</p>
</section>
@endsection
