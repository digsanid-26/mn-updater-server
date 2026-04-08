<?php

namespace App\Filament\Resources\LicenseKeyResource\Pages;

use App\Filament\Resources\LicenseKeyResource;
use App\Models\LicenseExclusion;
use App\Models\Plugin;
use App\Models\Theme;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicenseKey extends EditRecord
{
    protected static string $resource = LicenseKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $data = $this->form->getRawState();

        $this->syncExclusions(
            $record->id,
            'plugin',
            Plugin::where('is_active', true)->pluck('id')->toArray(),
            (array) ($data['excluded_plugins'] ?? [])
        );

        $this->syncExclusions(
            $record->id,
            'theme',
            Theme::where('is_active', true)->pluck('id')->toArray(),
            (array) ($data['excluded_themes'] ?? [])
        );
    }

    private function syncExclusions(int $licenseId, string $type, array $allIds, array $excludedIds): void
    {
        foreach ($allIds as $itemId) {
            LicenseExclusion::updateOrCreate(
                [
                    'license_key_id' => $licenseId,
                    'item_type'      => $type,
                    'item_id'        => $itemId,
                ],
                [
                    'is_excluded' => in_array((string) $itemId, array_map('strval', $excludedIds)),
                ]
            );
        }
    }
}
