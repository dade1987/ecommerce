<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\BomResource;
use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Filament\Resources\ProductionOrderResource\RelationManagers\ProductionPhasesRelationManager;
use App\Models\ProductionOrder;
use App\Services\OrderParsingService;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getModelLabel(): string
    {
        return __('filament-production.Ordine di Produzione');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-production.Ordini di Produzione');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament-production.Produzione');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer')
                    ->label(__('filament-production.Cliente'))
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => request()->get('customer')),
                Forms\Components\DatePicker::make('order_date')
                    ->label(__('filament-production.Data Ordine'))
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('status')
                    ->label(__('filament-production.Stato'))
                    ->options(OrderStatus::class)
                    ->required()
                    ->default(OrderStatus::PENDING),
                Forms\Components\Select::make('bom_id')
                    ->label(__('filament-production.Distinta Base'))
                    ->relationship('bom', 'internal_code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => request()->get('bom_id')),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('filament-production.Quantità'))
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Textarea::make('notes')
                    ->label(__('filament-production.Note'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament-production.ID Ordine'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->label(__('filament-production.Cliente'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('bom.internal_code')
                    ->label(__('filament-production.Codice BOM')),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('filament-production.Qtà'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-production.Stato'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('priority')
                    ->label(__('filament-production.Priorità'))
                    ->icon(fn (int $state): string => match ($state) {
                        0 => 'heroicon-o-arrow-down-circle',
                        1, 2 => 'heroicon-o-minus-circle',
                        3, 4 => 'heroicon-o-arrow-up-circle',
                        5 => 'heroicon-o-fire',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'gray',
                        1, 2 => 'info',
                        3, 4 => 'warning',
                        5 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label(__('filament-production.Data Ordine'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament-production.Filtra per Stato'))
                    ->options(OrderStatus::class)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Action::make('importFromFile')
                    ->label('Importa con AI')
                    ->icon('heroicon-o-document-arrow-up')
                    ->form([
                        Forms\Components\FileUpload::make('order_file')
                            ->label('File Ordine Cliente')
                            ->storeFiles(false)
                            ->required(),
                    ])
                    ->action(function (array $data, OrderParsingService $parsingService) {
                        try {
                            /** @var TemporaryUploadedFile $tempFile */
                            $tempFile = $data['order_file'];

                            $file = new UploadedFile(
                                $tempFile->getRealPath(),
                                $tempFile->getClientOriginalName(),
                                $tempFile->getMimeType(),
                                null,
                                true
                            );

                            $result = $parsingService->parseOrderFromFile($file);

                            if ($result) {
                                // Corrispondenza trovata!
                                $bom = $result['bom'];
                                $customer = $result['customer'];
                                $url = ProductionOrderResource::getUrl('create', [
                                    'customer' => $customer,
                                    'bom_id' => $bom->id,
                                ]);
                                return redirect($url);
                            } else {
                                // Nessuna corrispondenza.
                                Notification::make()
                                    ->title('Distinta Base non trovata')
                                    ->body('L\'IA non ha trovato una Distinta Base corrispondente nel file. Vuoi crearne una nuova manualmente?')
                                    ->warning()
                                    ->persistent()
                                    ->actions([
                                        NotificationAction::make('create_bom')
                                            ->label('Crea Distinta Base')
                                            ->button()
                                            ->url(BomResource::getUrl('create'))
                                            ->color('primary'),
                                    ])
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore di Importazione')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
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
            ProductionPhasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['bom']);
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Cliente' => $record->customer,
            'Prodotto' => $record->bom?->internal_code,
        ];
    }
}
