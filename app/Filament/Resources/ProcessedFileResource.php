<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessedFileResource\Pages;
use App\Filament\Resources\ProcessedFileResource\RelationManagers;
use App\Models\ProcessedFile;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ProcessedFileResource extends Resource
{
    protected static ?string $model = ProcessedFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('extractor_id')
                    ->relationship('extractor', 'slug')
                    ->disabled(),
                TextInput::make('original_filename')
                    ->disabled(),
                TextInput::make('file_path')
                    ->disabled(),
                TextInput::make('mime_type')
                    ->disabled(),
                TextInput::make('status')
                    ->disabled(),
                KeyValue::make('gemini_response')
                    ->disabled(),
                Textarea::make('error_message')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('extractor.slug')->sortable()->searchable(),
                TextColumn::make('original_filename')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('download')
                    ->label('Scarica file')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (ProcessedFile $record) {
                        return response()->download(
                            storage_path('app/public/' . $record->file_path),
                            $record->original_filename
                        );
                    })
                    ->visible(fn (ProcessedFile $record): bool => $record->file_path && Storage::disk('public')->exists($record->file_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcessedFiles::route('/'),
            'view' => Pages\ViewProcessedFile::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
