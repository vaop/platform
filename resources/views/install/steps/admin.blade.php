@extends('install.layout', ['step' => 'admin'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.admin.title') }}</h2>

    <p class="text-gray-600 mb-6">
        {{ __('install.admin.description') }}
    </p>

    <form action="{{ route('install.admin.store') }}" method="POST">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.admin.name') }}</label>
                <input type="text" name="name" id="name"
                       value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.admin.name_placeholder') }}"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.admin.email') }}</label>
                <input type="email" name="email" id="email"
                       value="{{ old('email') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.admin.email_placeholder') }}"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.admin.password') }}</label>
                <input type="password" name="password" id="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.admin.password_placeholder') }}"
                       required
                       minlength="8">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.admin.password_confirm') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="{{ __('install.admin.password_confirm_placeholder') }}"
                       required
                       minlength="8">
            </div>
        </div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('install.environment') }}"
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
