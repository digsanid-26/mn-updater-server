<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpdateLogResource\Pages;
use App\Models\UpdateLog;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UpdateLogResource extends Resource
{
    protected static ?string $model = UpdateLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Update Logs';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            FormComponents\TextInput::make('domain_name'),
            FormComponents\TextInput::make('plugin_slug'),
            FormComponents\TextInput::make('from_version'),
            FormComponents\TextInput::make('to_version'),
            FormComponents\TextInput::make('action'),
            FormComponents\TextInput::make('status'),
            FormComponents\TextInput::make('ip_address'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'download' => 'success',
                        'check_update' => 'info',
                        'validate_license' => 'warning',
                        'deactivate_license' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'denied' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('domain_name')
                    ->label('Domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plugin_slug')
                    ->label('Plugin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('from_version')
                    ->label('From'),
                Tables\Columns\TextColumn::make('to_version')
                    ->label('To'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'check_update' => 'Check Update',
                        'download' => 'Download',
                        'validate_license' => 'Validate License',
                        'deactivate_license' => 'Deactivate License',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'denied' => 'Denied',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUpdateLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
