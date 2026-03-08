<?php

namespace App\Filament\Resources\LicenseKeyResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    protected static ?string $title = 'Registered Domains';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            FormComponents\TextInput::make('domain')
                ->required()
                ->maxLength(255)
                ->helperText('e.g. "clientsite.com" (without http/www)'),
            FormComponents\TextInput::make('site_url')
                ->label('Site URL')
                ->url()
                ->maxLength(255),
            FormComponents\Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('site_url')
                    ->label('Site URL')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('registered_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_check_at')
                    ->label('Last Check')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['registered_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
