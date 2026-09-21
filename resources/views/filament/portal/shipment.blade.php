<x-filament-panels::page>
    <x-filament::section :heading="$tracking['status_label']" :description="$tracking['status_detail']">
        <dl class="mb-6 grid gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Tracking number</dt>
                <dd class="font-mono text-sm text-gray-950 dark:text-white">{{ $tracking['tracking_number'] }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Route</dt>
                <dd class="text-sm text-gray-950 dark:text-white">{{ $tracking['route'] }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Expected</dt>
                <dd class="text-sm text-gray-950 dark:text-white">{{ $tracking['estimated_delivery_text'] ?? 'On request' }}</dd>
            </div>
        </dl>

        <ol class="space-y-4">
            @foreach ($tracking['timeline'] as $step)
                <li class="flex gap-3">
                    <span @class([
                        'mt-1.5 size-3 shrink-0 rounded-full',
                        'bg-primary-500' => $step['reached'],
                        'bg-gray-300 dark:bg-gray-600' => ! $step['reached'],
                    ])></span>
                    <div>
                        <p @class(['text-sm font-medium', 'text-gray-950 dark:text-white' => $step['reached'], 'text-gray-500' => ! $step['reached']])>
                            {{ $step['label'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if ($step['occurred_at'])
                                {{ \Illuminate\Support\Carbon::parse($step['occurred_at'])->format('D j M Y · H:i') }}
                                @if ($step['location']) · {{ $step['location'] }} @endif
                            @else
                                {{ $step['detail'] }}
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>

        @if ($tracking['proof_of_delivery_url'])
            <div class="mt-6">
                <x-filament::link :href="$tracking['proof_of_delivery_url']" target="_blank">
                    View proof of delivery
                </x-filament::link>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
