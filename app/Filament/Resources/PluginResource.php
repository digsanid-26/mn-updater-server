<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginResource\Pages;
use App\Filament\Resources\PluginResource\RelationManagers\VersionsRelationManager;
use App\Models\Plugin;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PluginResource extends Resource
{
    protected static ?string $model = Plugin::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string | \UnitEnum | null $navigationGroup = 'Plugin Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            LayoutComponents\Section::make('Plugin Information')->schema([
                FormComponents\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FormComponents\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Folder name, e.g. "mn-effects"'),
                FormComponents\TextInput::make('file_slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Full slug, e.g. "mn-effects/mn-effects.php"'),
                FormComponents\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),

            LayoutComponents\Section::make('Author & Links')->schema([
                FormComponents\TextInput::make('author')
                    ->default('Digsan-Id')
                    ->maxLength(255),
                FormComponents\TextInput::make('author_uri')
                    ->label('Author URI')
                    ->default('https://www.digsan.id/')
                    ->url()
                    ->maxLength(255),
                FormComponents\TextInput::make('homepage')
                    ->url()
                    ->maxLength(255),
            ])->columns(3),

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
                FormComponents\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latest_version.version')
                    ->label('Latest Version')
                    ->badge()
                    ->color('success')
                    ->placeholder('No versions'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('versions_count')
                    ->counts('versions')
                    ->label('Versions'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlugins::route('/'),
            'create' => Pages\CreatePlugin::route('/create'),
            'edit' => Pages\EditPlugin::route('/{record}/edit'),
        ];
    }
}
