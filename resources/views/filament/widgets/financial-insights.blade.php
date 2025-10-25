<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getViewData()['insights']['title'] }}
        </x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach($this->getViewData()['insights']['items'] as $item)
                <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <x-filament::icon 
                                :icon="$item['icon']" 
                                class="h-8 w-8 text-{{ $item['color'] }}-600"
                            />
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item['title'] }}
                                </dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $item['value'] }}
                                </dd>
                                <dd class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $item['description'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
            <div class="flex">
                <div class="shrink-0">
                    <x-filament::icon 
                        icon="heroicon-o-information-circle" 
                        class="h-5 w-5 text-blue-400"
                    />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Profile Type: {{ $this->getViewData()['profileType'] }}
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>
                            @switch($this->getViewData()['profileType'])
                                @case('Student')
                                    Focus on building skills while managing expenses. Consider part-time work or internships.
                                    @break
                                @case('Employee')
                                    Maintain a steady income flow and focus on career growth and savings.
                                    @break
                                @case('Business Owner')
                                    Monitor cash flow closely and reinvest profits for business growth.
                                    @break
                                @case('Freelancer')
                                    Diversify income sources and maintain an emergency fund for irregular income.
                                    @break
                                @default
                                    Update your employment information to get personalized financial insights.
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>