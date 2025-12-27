<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('install.title') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-sm">
            <div class="max-w-3xl mx-auto px-4 py-4">
                <h1 class="text-xl font-semibold text-gray-900">{{ __('install.header') }}</h1>
            </div>
        </header>

        <main class="flex-1 py-8">
            <div class="max-w-3xl mx-auto px-4">
                @if(isset($step))
                <nav class="mb-8">
                    <ol class="flex items-center space-x-2 text-sm">
                        @php
                            $steps = [
                                'welcome' => __('install.steps.welcome'),
                                'requirements' => __('install.steps.requirements'),
                                'database' => __('install.steps.database'),
                                'environment' => __('install.steps.environment'),
                                'admin' => __('install.steps.admin'),
                                'finalize' => __('install.steps.finalize'),
                            ];
                            $currentFound = false;
                        @endphp
                        @foreach($steps as $key => $label)
                            @php
                                $isCurrent = $step === $key;
                                if ($isCurrent) $currentFound = true;
                                $isPast = !$currentFound && !$isCurrent;
                            @endphp
                            <li class="flex items-center">
                                <span class="@if($isCurrent) text-blue-600 font-medium @elseif($isPast) text-gray-500 @else text-gray-400 @endif">
                                    {{ $label }}
                                </span>
                                @if(!$loop->last)
                                    <svg class="w-4 h-4 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {{ session('error') }}
                </div>
                @endif

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('success') }}
                </div>
                @endif

                <div class="bg-white shadow-sm rounded-lg">
                    @yield('content')
                </div>
            </div>
        </main>

        <footer class="py-4 text-center text-sm text-gray-500">
            {{ __('install.footer') }}
        </footer>
    </div>
</body>
</html>
