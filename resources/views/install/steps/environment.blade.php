@extends('install.layout', ['step' => 'environment'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.environment.title') }}</h2>

    <p class="text-gray-600 mb-6">
        {{ __('install.environment.description') }}
    </p>

    <form action="{{ route('install.environment.store') }}" method="POST">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.environment.airline_name') }}</label>
                <input type="text" name="app_name" id="app_name"
                       value="{{ old('app_name', $config['app_name']) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.environment.airline_name_placeholder') }}">
                @error('app_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="app_url" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.environment.app_url') }}</label>
                <input type="url" name="app_url" id="app_url"
                       value="{{ old('app_url', $config['app_url']) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.environment.app_url_placeholder') }}">
                <p class="mt-1 text-sm text-gray-500">{{ __('install.environment.app_url_hint') }}</p>
                @error('app_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.environment.timezone') }}</label>
                <select name="timezone" id="timezone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $config['timezone']) === $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
                @error('timezone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('install.database') }}"
               class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                {{ __('install.back') }}
            </a>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ __('install.continue') }}
            </button>
        </div>
    </form>
</div>
@endsection
