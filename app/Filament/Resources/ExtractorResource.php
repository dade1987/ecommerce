<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtractorResource\Pages;
use App\Filament\Resources\ExtractorResource\RelationManagers;
use App\Models\Extractor;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\File;

class ExtractorResource extends Resource
{
    protected static ?string $model = Extractor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Select::make('export_format')
                    ->options([
                        'json' => 'JSON',
                        'excel' => 'Excel',
                        'csv' => 'CSV',
                    ])
                    ->required()
                    ->default('json')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $state === 'json' ? $set('export_class', null) : null),
                self::getExportClassSelect(),
                Textarea::make('prompt')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('export_format')
                    ->searchable(),
                Tables\Columns\TextColumn::make('export_class')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListExtractors::route('/'),
            'create' => Pages\CreateExtractor::route('/create'),
            'edit' => Pages\EditExtractor::route('/{record}/edit'),
        ];
    }

    /**
     * Componente Select per scegliere la classe di esportazione.
     * Visibile solo quando il formato di esportazione è 'excel'.
     */
    protected static function getExportClassSelect(): Select
    {
        $exportFiles = File::files(app_path('Exports'));
        $options = [];
        foreach ($exportFiles as $file) {
            $className = $file->getBasename('.php');
            $options[$className] = $className;
        }

        return Select::make('export_class')
            ->label('Classe di Esportazione')
            ->options($options)
            ->required()
            ->visible(fn (callable $get) => in_array($get('export_format'), ['excel', 'csv']));
    }
}
