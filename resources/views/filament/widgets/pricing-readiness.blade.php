<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-exclamation-triangle"
        icon-color="warning"
        heading="Pricing needs attention"
        description="These figures were missing or contradictory in the source rate card."
    >
        <ul class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($this->getIssues() as $issue)
                <li class="py-3 first:pt-0 last:pb-0">
                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $issue['title'] }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $issue['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
