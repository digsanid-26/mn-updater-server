<?php

namespace App\Filament\Resources\PluginResource\RelationManagers;

use App\Models\PluginVersion;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                ->visibility('private')
                ->preserveFilenames()
                ->columnSpanFull()
                ->helperText('Upload the plugin .zip file. Leave empty to keep existing file.'),
            FormComponents\Placeholder::make('current_file')
                ->label('Current File')
                ->content(fn ($record) => $record?->file_name 
                    ? "{$record->file_name} (" . number_format($record->file_size / 1024, 1) . " KB)" 
                    : 'No file uploaded')
                ->columnSpanFull()
                ->hiddenOn('create'),
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

    /**
     * Process uploaded zip file and move to permanent location
     */
    protected function processZipFile(PluginVersion $version, ?string $tempPath): void
    {
        if (empty($tempPath)) {
            return;
        }

        $plugin = $version->plugin;
        $disk = Storage::disk('local');

        // Build permanent file path
        $fileName = $plugin->slug . '-' . $version->version . '.zip';
        $directory = 'plugins/' . $plugin->slug;
        $permanentPath = $directory . '/' . $fileName;

        // Ensure directory exists
        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        // Move file from temp to permanent location
        if ($disk->exists($tempPath)) {
            // Delete old file if exists
            if ($version->file_path && $disk->exists($version->file_path)) {
                $disk->delete($version->file_path);
            }

            // Move to permanent location
            $disk->move($tempPath, $permanentPath);

            // Calculate file info
            $fullPath = $disk->path($permanentPath);
            $checksum = hash_file('sha256', $fullPath);
            $fileSize = $disk->size($permanentPath);

            // Update version record
            $version->update([
                'file_path' => $permanentPath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'checksum' => $checksum,
            ]);
        }
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
                    ->label('File')
                    ->placeholder('No file'),
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
                Actions\CreateAction::make()
                    ->after(function (PluginVersion $record, array $data) {
                        $this->processZipFile($record, $data['zip_file'] ?? null);
                    }),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->after(function (PluginVersion $record, array $data) {
                        $this->processZipFile($record, $data['zip_file'] ?? null);
                    }),
                Actions\DeleteAction::make()
                    ->before(function (PluginVersion $record) {
                        // Delete the zip file when version is deleted
                        if ($record->file_path) {
                            Storage::disk('local')->delete($record->file_path);
                        }
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
