@php
    $record = $getRecord();
@endphp

<div class="fi-ta-card rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden transition hover:shadow-md">
    <div class="p-6">
        {{-- Header Section --}}
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <x-filament::icon
                            icon="heroicon-o-building-office-2"
                            class="h-6 w-6 text-primary-600 dark:text-primary-400"
                        />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                            {{ $record->company_name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $record->position }}
                        </p>
                    </div>
                </div>
            </div>

            @if($record->is_current)
                <span class="inline-flex items-center gap-x-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30">
                    <svg class="h-1.5 w-1.5 fill-success-500" viewBox="0 0 6 6" aria-hidden="true">
                        <circle cx="3" cy="3" r="3" />
                    </svg>
                    Current
                </span>
            @endif
        </div>

        {{-- Divider --}}
        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

        {{-- Profile & Employment Type Badges --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="inline-flex items-center gap-x-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium
                @if($record->profile_type === 'student') bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30
                @elseif($record->profile_type === 'employee') bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20
                @elseif($record->profile_type === 'business_owner') bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20
                @elseif($record->profile_type === 'freelancer') bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/10 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/30
                @endif">
                <x-filament::icon
                    icon="{{ match($record->profile_type) {
                        'student' => 'heroicon-o-academic-cap',
                        'employee' => 'heroicon-o-building-office',
                        'business_owner' => 'heroicon-o-building-storefront',
                        'freelancer' => 'heroicon-o-user',
                        default => 'heroicon-o-user'
                    } }}"
                    class="h-4 w-4"
                />
                {{ match($record->profile_type) {
                    'student' => 'Student',
                    'employee' => 'Employee',
                    'business_owner' => 'Business Owner',
                    'freelancer' => 'Freelancer',
                    default => $record->profile_type
                } }}
            </span>

            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                {{ match($record->employment_type) {
                    'full_time' => 'Full Time',
                    'part_time' => 'Part Time',
                    'contract' => 'Contract',
                    'internship' => 'Internship',
                    'freelance' => 'Freelance',
                    default => $record->employment_type
                } }}
            </span>
        </div>

        {{-- Duration --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-calendar" class="h-5 w-5 text-success-500" />
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Start Date</p>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $record->start_date?->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5 text-danger-500" />
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">End Date</p>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $record->end_date?->format('M d, Y') ?? 'Present' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

        {{-- Financial Info --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 p-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5 text-warning-500" />
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Salary</p>
                    </div>
                    <p class="text-lg font-bold text-warning-600 dark:text-warning-400">
                        @if($record->salary)
                            ${{ number_format($record->salary, 2) }}
                            @if($record->salary_frequency)
                                <span class="text-xs font-normal text-gray-500">
                                    {{ match($record->salary_frequency) {
                                        'hourly' => '/hr',
                                        'monthly' => '/mo',
                                        'yearly' => '/yr',
                                        default => ''
                                    } }}
                                </span>
                            @endif
                        @else
                            <span class="text-sm font-normal text-gray-400">Not specified</span>
                        @endif
                    </p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <x-filament::icon icon="heroicon-o-currency-dollar" class="h-5 w-5 text-success-500" />
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Earned</p>
                    </div>
                    <p class="text-lg font-bold text-success-600 dark:text-success-400">
                        ${{ number_format($record->incomes->sum('amount'), 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($record->description)
            <div class="mt-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 italic line-clamp-3">
                    <x-filament::icon icon="heroicon-o-document-text" class="inline h-4 w-4 mr-1" />
                    {{ $record->description }}
                </p>
            </div>
        @endif
    </div>

    {{-- Actions Footer --}}
    <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-6 py-3">
        <a href="{{ \App\Filament\Resources\EmploymentHistoryResource::getUrl('edit', ['record' => $record]) }}"
           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg shadow-sm transition duration-75 outline-none focus-visible:ring-2 bg-primary-600 text-white hover:bg-primary-500 focus-visible:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400">
            <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
            <span>Edit</span>
        </a>

        <button type="button"
                wire:click="mountTableAction('delete', '{{ $record->id }}')"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg shadow-sm transition duration-75 outline-none focus-visible:ring-2 bg-danger-600 text-white hover:bg-danger-500 focus-visible:ring-danger-500/50 dark:bg-danger-500 dark:hover:bg-danger-400">
            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
            <span>Delete</span>
        </button>
    </div>
</div>
