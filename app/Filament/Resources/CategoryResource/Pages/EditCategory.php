<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('This will permanently delete the category and may affect related income/expense records.')
                ->before(function () {
                    if ($this->record->incomes()->count() > 0 || $this->record->expenses()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Cannot delete category')
                            ->body('This category has associated transactions. Please remove them first or consider deactivating the category instead.')
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Category updated successfully';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure color has a value
        if (empty($data['color'])) {
            $data['color'] = '#3b82f6';
        }

        return $data;
    }
}
