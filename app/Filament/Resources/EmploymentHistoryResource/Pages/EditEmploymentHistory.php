<?php

namespace App\Filament\Resources\EmploymentHistoryResource\Pages;

use App\Filament\Resources\EmploymentHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmploymentHistory extends EditRecord
{
    protected static string $resource = EmploymentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this is set as current position, make sure no other employment is current
        if ($data['is_current']) {
            \App\Models\EmploymentHistory::where('user_id', auth()->id())
                ->where('id', '!=', $this->record->id)
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