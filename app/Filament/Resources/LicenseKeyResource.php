<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseKeyResource\Pages;
use App\Filament\Resources\LicenseKeyResource\RelationManagers\DomainsRelationManager;
use App\Models\LicenseExclusion;
use App\Models\LicenseKey;
use App\Models\Plugin;
use App\Models\Theme;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LicenseKeyResource extends Resource
{
    protected static ?string $model = LicenseKey::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static string | \UnitEnum | null $navigationGroup = 'License Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            LayoutComponents\Section::make('License Information')->schema([
                FormComponents\TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->default(fn () => LicenseKey::generateKey())
                    ->helperText('Auto-generated. You can customize it.'),
                FormComponents\TextInput::make('client_name')
                    ->required()
                    ->maxLength(255),
                FormComponents\TextInput::make('client_email')
                    ->email()
                    ->maxLength(255),
            ])->columns(3),

            LayoutComponents\Section::make('Settings')->schema([
                FormComponents\TextInput::make('max_domains')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(100),
                FormComponents\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                FormComponents\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->nullable()
                    ->helperText('Leave empty for no expiration'),
            ])->columns(3),

            LayoutComponents\Section::make('Notes')->schema([
                FormComponents\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

            LayoutComponents\Section::make('Plugin & Theme Access')
                ->description('By default all plugins and themes are accessible. Toggle "Excluded" to revoke install, activate, and update access for this license.')
                ->schema([
                    FormComponents\Placeholder::make('plugin_access_header')
                        ->label('Plugins')
                        ->content(''),
                    FormComponents\CheckboxList::make('excluded_plugins')
                        ->label('')
                        ->options(fn () => Plugin::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray())
                        ->descriptions(fn () => Plugin::where('is_active', true)->orderBy('name')->pluck('slug', 'id')->toArray())
                        ->columns(2)
                        ->helperText('Checked = excluded (no install / activate / update)')
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (! $record) return;
                            $excluded = LicenseExclusion::where('license_key_id', $record->id)
                                ->where('item_type', 'plugin')
                                ->where('is_excluded', true)
                                ->pluck('item_id')
                                ->toArray();
                            $component->state($excluded);
                        }),

                    FormComponents\Placeholder::make('theme_access_header')
                        ->label('Themes')
                        ->content(''),
                    FormComponents\CheckboxList::make('excluded_themes')
                        ->label('')
                        ->options(fn () => Theme::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray())
                        ->descriptions(fn () => Theme::where('is_active', true)->orderBy('name')->pluck('slug', 'id')->toArray())
                        ->columns(2)
                        ->helperText('Checked = excluded (no install / activate / update)')
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (! $record) return;
                            $excluded = LicenseExclusion::where('license_key_id', $record->id)
                                ->where('item_type', 'theme')
                                ->where('is_excluded', true)
                                ->pluck('item_id')
                                ->toArray();
                            $component->state($excluded);
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('max_domains')
                    ->label('Max')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('domains_count')
                    ->counts('domains')
                    ->label('Active Domains')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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
            DomainsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenseKeys::route('/'),
            'create' => Pages\CreateLicenseKey::route('/create'),
            'edit' => Pages\EditLicenseKey::route('/{record}/edit'),
        ];
    }
}
