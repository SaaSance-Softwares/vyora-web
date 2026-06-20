@extends('layouts.store')

@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description)

@section('content')
    @if($page->content && is_array($page->content))
        @foreach($page->content as $section)
            @if(view()->exists('store.components.' . $section['type']))
                @include('store.components.' . $section['type'], ['data' => $section['data']])
            @else
                {{-- Debug: Component not found --}}
                {{-- <div class="p-4 bg-red-100 text-red-700">Component {{ $section['type'] }} not found.</div> --}}
            @endif
        @endforeach
    @else
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ $page->title }}</h1>
            <p class="text-lg text-gray-500">This page is currently empty.</p>
        </div>
    @endif
@endsection