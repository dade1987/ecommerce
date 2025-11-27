<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteSearchResource\Pages;
use App\Filament\Resources\WebsiteSearchResource\RelationManagers;
use App\Models\WebsiteSearch;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WebsiteSearchResource extends Resource
{
    protected static ?string $model = WebsiteSearch::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Ricerche Effettuate';

    protected static ?string $modelLabel = 'Ricerca';

    protected static ?string $pluralModelLabel = 'Ricerche';

    protected static ?string $navigationGroup = 'AI & Chatbot';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('website')
                    ->label('Sito Web')
                    ->url()
                    ->maxLength(255),
                Textarea::make('query')
                    ->label('Query di Ricerca')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('locale')
                    ->label('Locale')
                    ->maxLength(10)
                    ->default('it'),
                Textarea::make('response')
                    ->label('Risposta')
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('content_length')
                    ->label('Lunghezza Contenuto')
                    ->numeric(),
                Toggle::make('from_cache')
                    ->label('Da Cache')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('website')
                    ->label('Sito Web')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('query')
                    ->label('Query')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label('Locale')
                    ->badge(),
                TextColumn::make('content_length')
                    ->label('Lunghezza')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state).' caratteri' : '-')
                    ->sortable(),
                BooleanColumn::make('from_cache')
                    ->label('Cache')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('from_cache')
                    ->label('Da Cache')
                    ->query(fn (Builder $query): Builder => $query->where('from_cache', true)),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Da'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('A'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListWebsiteSearches::route('/'),
            'create' => Pages\CreateWebsiteSearch::route('/create'),
            'edit' => Pages\EditWebsiteSearch::route('/{record}/edit'),
        ];
    }
}
