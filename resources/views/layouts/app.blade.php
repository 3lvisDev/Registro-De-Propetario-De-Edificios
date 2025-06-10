<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Lista de Copropietarios
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('copropietarios.index') }}" class="mb-4">
                    <input type="text" name="buscar" placeholder="Buscar por nombre o departamento"
                        value="{{ request('buscar') }}" class="border rounded px-2 py-1 mr-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded">Buscar</button>
                </form>

                @foreach($departamentos as $numero)
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Departamento {{ $numero }}</h3>
                        <ul class="list-disc pl-6">
                            @foreach($agrupado[$numero] as $copropietario)
                                <li class="text-gray-700 dark:text-gray-300">{{ $copropietario->nombre_completo }} ({{ $copropietario->tipo }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

