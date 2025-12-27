@extends('install.layout')

@section('content')
<div class="p-6 text-center">
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.complete.title') }}</h2>

    <p class="text-gray-600 mb-8">
        {{ __('install.complete.description') }}
    </p>

    <a href="/"
       class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        {{ __('install.complete.go_home') }}
    </a>
</div>
@endsection
