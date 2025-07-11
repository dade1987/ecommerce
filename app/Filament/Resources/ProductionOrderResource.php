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

    protected static ?string $modelLabel = 'Ordine di Produzione';

    protected static ?string $pluralModelLabel = 'Ordini di Produzione';

    protected static ?string $navigationGroup = 'Produzione';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer')
                    ->label('Cliente')
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => request()->get('customer')),
                Forms\Components\DatePicker::make('order_date')
                    ->label('Data Ordine')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options(OrderStatus::class)
                    ->required()
                    ->default(OrderStatus::PENDING),
                Forms\Components\Select::make('bom_id')
                    ->label('Distinta Base')
                    ->relationship('bom', 'product_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => request()->get('bom_id')),
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Ordine')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bom.product_name')
                    ->label('Prodotto (Distinta Base)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Data Ordine')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtra per Stato')
                    ->options(OrderStatus::class)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Action::make('importFromFile')
                    ->label('Importa da File')
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
            'Prodotto' => $record->bom?->product_name,
        ];
    }
}
