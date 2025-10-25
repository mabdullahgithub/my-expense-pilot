<?php

namespace App\Filament\Resources\EmploymentHistoryResource\Pages;

use App\Filament\Resources\EmploymentHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmploymentHistory extends CreateRecord
{
    protected static string $resource = EmploymentHistoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // If this is set as current position, make sure no other employment is current
        if ($data['is_current']) {
            \App\Models\EmploymentHistory::where('user_id', auth()->id())
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}