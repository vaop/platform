@extends('install.layout', ['step' => 'finalize'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.finalize.title') }}</h2>

    @if($step === 'ready')
        <p class="text-gray-600 mb-6">
            {{ __('install.finalize.ready_description') }}
        </p>

        <ul class="list-disc list-inside text-gray-600 mb-6 space-y-2">
            <li>{{ __('install.finalize.will_create_tables', ['count' => $progress['total']]) }}</li>
            <li>{{ __('install.finalize.will_create_admin', ['email' => $admin_email]) }}</li>
            <li>{{ __('install.finalize.will_finalize') }}</li>
        </ul>

        <form action="{{ route('install.finalize.store') }}" method="POST">
            @csrf
            <input type="hidden" name="step" value="migrate">
            <div class="flex justify-between mt-8">
                <a href="{{ route('install.admin') }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                    {{ __('install.back') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('install.finalize.install_button') }}
                </button>
            </div>
        </form>
    @else
        <div class="mb-6">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>{{ __('install.finalize.installing') }}</span>
                <span>{{ $progress['percent'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                     style="width: {{ $progress['percent'] }}%"></div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700">
                    @switch($next_step)
                        @case('migrate')
                            {{ __('install.finalize.running_migrations', ['completed' => $progress['completed'], 'total' => $progress['total']]) }}
                            @break
                        @case('user')
                            {{ __('install.finalize.creating_admin') }}
                            @break
                        @case('optimize')
                            {{ __('install.finalize.optimizing') }}
                            @break
                        @case('complete')
                            {{ __('install.finalize.completing') }}
                            @break
                    @endswitch
                </span>
            </div>
        </div>

        <p class="text-sm text-gray-500">
            {{ __('install.finalize.do_not_close') }}
        </p>

        <form id="continue-form" action="{{ route('install.finalize.store') }}" method="POST">
            @csrf
            <input type="hidden" name="step" value="{{ $next_step }}">
        </form>

        <script>
            setTimeout(function() {
                document.getElementById('continue-form').submit();
            }, 300);
        </script>
    @endif
</div>
@endsection
