<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
     protected function getFormActions(): array
    {
        $isEditing = $this->data['is_editing'] ?? false;

        if (! $isEditing) {
            return [];
        }

        return parent::getFormActions();
    }

    // Save hone ke baad wapas normal mode me lane ke liye
    protected function afterSave(): void
    {
        $this->data['is_editing'] = false;
    }
}
