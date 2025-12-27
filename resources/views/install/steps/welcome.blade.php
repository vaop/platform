@extends('install.layout', ['step' => 'welcome'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.welcome.title') }}</h2>

    <p class="text-gray-600 mb-6">
        {{ __('install.welcome.description') }}
    </p>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-medium text-blue-900 mb-2">{{ __('install.welcome.before_begin') }}</h3>
        <ul class="text-blue-800 text-sm space-y-1 list-disc list-inside">
            <li>{{ __('install.welcome.checklist.database') }}</li>
            <li>{{ __('install.welcome.checklist.credentials') }}</li>
            <li>{{ __('install.welcome.checklist.php_version') }}</li>
        </ul>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('install.requirements') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            {{ __('install.get_started') }}
        </a>
    </div>
</div>
@endsection
