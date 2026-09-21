<x-filament-panels::page>
    {{ $this->form }}

    @php($report = $this->getReport())

    <x-filament::section :heading="$report['title']">
        @if (filled($report['totals']))
            <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($report['totals'] as $label => $value)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">{{ $label }}</p>
                        <p class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 dark:border-white/10">
                    <tr>
                        @foreach ($report['columns'] as $column)
                            <th class="px-3 py-2 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="px-3 py-2 whitespace-nowrap text-gray-950 dark:text-white">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="px-3 py-6 text-center text-gray-500">
                                Nothing matched those filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
