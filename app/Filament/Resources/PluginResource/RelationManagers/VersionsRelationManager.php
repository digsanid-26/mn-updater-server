<?php

namespace App\Filament\Resources\PluginResource\RelationManagers;

use App\Services\FileService;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Versions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            FormComponents\TextInput::make('version')
                ->required()
                ->maxLength(20)
                ->helperText('Semantic version, e.g. "1.1.0"'),
            FormComponents\Textarea::make('changelog')
                ->rows(5)
                ->columnSpanFull()
                ->helperText('Markdown format supported'),
            FormComponents\FileUpload::make('zip_file')
                ->label('Plugin ZIP')
                ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                ->disk('local')
                ->directory('plugins/uploads/temp')
                ->columnSpanFull()
                ->helperText('Upload the plugin .zip file'),
            LayoutComponents\Section::make('Requirements')->schema([
                FormComponents\TextInput::make('requires_php')
                    ->label('Requires PHP')
                    ->default('7.4')
                    ->maxLength(20),
                FormComponents\TextInput::make('requires_wp')
                    ->label('Requires WP')
                    ->default('5.8')
                    ->maxLength(20),
                FormComponents\TextInput::make('tested_wp')
                    ->label('Tested WP')
                    ->maxLength(20),
                FormComponents\DateTimePicker::make('released_at')
                    ->label('Release Date')
                    ->default(now()),
            ])->columns(4),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File'),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1) . ' KB' : '-'),
                Tables\Columns\TextColumn::make('released_at')
                    ->label('Released')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('released_at', 'desc')
            ->filters([])
            ->headerActions([
                Actions\CreateAction::make(),
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
