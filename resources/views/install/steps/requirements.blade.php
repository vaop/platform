@extends('install.layout', ['step' => 'requirements'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.requirements.title') }}</h2>

    <p class="text-gray-600 mb-6">
        {{ __('install.requirements.description') }}
    </p>

    <div class="space-y-6">
        <div>
            <h3 class="font-medium text-gray-900 mb-3">{{ __('install.requirements.php_version') }}</h3>
            <div class="flex items-center justify-between p-3 rounded-lg {{ $checks['php']['passed'] ? 'bg-green-50' : 'bg-red-50' }}">
                <span class="text-sm">
                    {{ __('install.requirements.php_required', ['version' => $checks['php']['required']]) }}
                    <span class="text-gray-500">({{ __('install.requirements.current_version', ['version' => $checks['php']['current']]) }})</span>
                </span>
                @if($checks['php']['passed'])
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                @endif
            </div>
        </div>

        <div>
            <h3 class="font-medium text-gray-900 mb-3">{{ __('install.requirements.extensions') }}</h3>
            <div class="grid grid-cols-2 gap-2">
                @foreach($checks['extensions'] as $extension)
                <div class="flex items-center justify-between p-3 rounded-lg {{ $extension['passed'] ? 'bg-green-50' : 'bg-red-50' }}">
                    <span class="text-sm">{{ $extension['name'] }}</span>
                    @if($extension['passed'])
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-medium text-gray-900 mb-3">{{ __('install.requirements.directories') }}</h3>
            <div class="space-y-2">
                @foreach($checks['directories'] as $directory)
                <div class="flex items-center justify-between p-3 rounded-lg {{ $directory['passed'] ? 'bg-green-50' : 'bg-red-50' }}">
                    <span class="text-sm font-mono">{{ $directory['path'] }}</span>
                    @if($directory['passed'])
                        <span class="text-green-600 text-sm">{{ __('install.requirements.writable') }}</span>
                    @else
                        <span class="text-red-600 text-sm">
                            {{ $directory['exists'] ? __('install.requirements.not_writable') : __('install.requirements.missing') }}
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex justify-between mt-8">
        <a href="{{ route('install.welcome') }}"
           class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
            {{ __('install.back') }}
        </a>

        @if($checks['passed'])
        <form action="{{ route('install.requirements.check') }}" method="POST">
            @csrf
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ __('install.continue') }}
            </button>
        </form>
        @else
        <button type="button" disabled
                class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
            {{ __('install.requirements.fix_issues') }}
        </button>
        @endif
    </div>
</div>
@endsection
