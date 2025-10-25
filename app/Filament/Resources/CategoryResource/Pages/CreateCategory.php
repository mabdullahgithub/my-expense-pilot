<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Set default color if not provided
        if (empty($data['color'])) {
            $data['color'] = '#3b82f6';
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Category created successfully';
    }

    protected function afterCreate(): void
    {
        // Send additional notification with category details
        \Filament\Notifications\Notification::make()
            ->title('Category "' . $this->record->name . '" created')
            ->body('You can now use this category for your income and expense tracking.')
            ->success()
            ->send();
    }
}
