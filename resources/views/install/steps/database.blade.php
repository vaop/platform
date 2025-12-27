@extends('install.layout', ['step' => 'database'])

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('install.database.title') }}</h2>

    <p class="text-gray-600 mb-6">
        {{ __('install.database.description') }}
    </p>

    <form action="{{ route('install.database.store') }}" method="POST" id="database-form">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="driver" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.type') }}</label>
                <select name="driver" id="driver"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="mysql" {{ old('driver', $config['driver']) === 'mysql' ? 'selected' : '' }}>{{ __('install.database.mysql') }}</option>
                </select>
            </div>

            <div id="connection-fields">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="host" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.host') }}</label>
                        <input type="text" name="host" id="host"
                               value="{{ old('host', $config['host']) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="127.0.0.1">
                    </div>

                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.port') }}</label>
                        <input type="number" name="port" id="port"
                               value="{{ old('port', $config['port']) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="3306">
                    </div>
                </div>

                <div class="mt-4">
                    <label for="database" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.name') }}</label>
                    <input type="text" name="database" id="database"
                           value="{{ old('database', $config['database']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="vaop">
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.username') }}</label>
                        <input type="text" name="username" id="username"
                               value="{{ old('username', $config['username']) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="root">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('install.database.password') }}</label>
                        <input type="password" name="password" id="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="">
                    </div>
                </div>
            </div>
        </div>

        <div id="test-result" class="mt-4 hidden"></div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('install.requirements') }}"
               class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                {{ __('install.back') }}
            </a>

            <div class="flex space-x-3">
                <button type="button" id="test-connection"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('install.database.test_connection') }}
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('install.continue') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testButton = document.getElementById('test-connection');
    const testResult = document.getElementById('test-result');
    const testingText = @json(__('install.database.testing'));
    const testConnectionText = @json(__('install.database.test_connection'));
    const connectionFailedText = @json(__('install.database.connection_failed'));

    testButton.addEventListener('click', async function() {
        const formData = new FormData(document.getElementById('database-form'));
        testButton.disabled = true;
        testButton.textContent = testingText;
        testResult.className = 'mt-4 p-4 rounded-lg';

        try {
            const response = await fetch('{{ route('install.database.test') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                testResult.className = 'mt-4 p-4 rounded-lg bg-green-50 text-green-700';
                testResult.textContent = result.message;
            } else {
                testResult.className = 'mt-4 p-4 rounded-lg bg-red-50 text-red-700';
                testResult.textContent = result.message;
            }
            testResult.classList.remove('hidden');
        } catch (error) {
            testResult.className = 'mt-4 p-4 rounded-lg bg-red-50 text-red-700';
            testResult.textContent = connectionFailedText;
            testResult.classList.remove('hidden');
        }

        testButton.disabled = false;
        testButton.textContent = testConnectionText;
    });
});
</script>
@endsection
