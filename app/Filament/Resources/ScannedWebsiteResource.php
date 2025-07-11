<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScannedWebsiteResource\Pages;
use App\Filament\Resources\ScannedWebsiteResource\RelationManagers;
use App\Models\ScannedWebsite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScannedWebsiteResource extends Resource
{
    protected static ?string $model = ScannedWebsite::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Siti Scansionati';

    protected static ?string $modelLabel = 'Sito Scansionato';

    protected static ?string $pluralModelLabel = 'Siti Scansionati';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->label('Dominio')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->label('Telefono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('risk_percentage')
                    ->label('Percentuale di Rischio')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('ip_address')
                    ->label('Indirizzo IP')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('scanned_at')
                    ->label('Data Scansione')
                    ->required(),
                Forms\Components\Textarea::make('critical_points')
                    ->label('Punti Critici')
                    ->rows(3),
                Forms\Components\Textarea::make('raw_data')
                    ->label('Dati Grezzi')
                    ->rows(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->label('Dominio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefono')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('risk_percentage')
                    ->label('Rischio %')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 30 => 'success',
                        $state <= 70 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Scansionato il')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('high_risk')
                    ->label('Alto Rischio')
                    ->query(fn (Builder $query): Builder => $query->where('risk_percentage', '>', 70)),
                Tables\Filters\Filter::make('medium_risk')
                    ->label('Medio Rischio')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('risk_percentage', [31, 70])),
                Tables\Filters\Filter::make('low_risk')
                    ->label('Basso Rischio')
                    ->query(fn (Builder $query): Builder => $query->where('risk_percentage', '<=', 30)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scanned_at', 'desc');
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
            'index' => Pages\ListScannedWebsites::route('/'),
            'create' => Pages\CreateScannedWebsite::route('/create'),
            'view' => Pages\ViewScannedWebsite::route('/{record}'),
            'edit' => Pages\EditScannedWebsite::route('/{record}/edit'),
        ];
    }
}
