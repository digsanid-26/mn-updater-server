<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThemeResource\Pages;
use App\Filament\Resources\ThemeResource\RelationManagers\VersionsRelationManager;
use App\Models\Theme;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string | \UnitEnum | null $navigationGroup = 'Theme Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            LayoutComponents\Section::make('Theme Information')->schema([
                FormComponents\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FormComponents\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Theme directory name, e.g. "mn-flavor"'),
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
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}
