<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductTwin;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class InvoiceScan extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * @var Form
     */
    public $form;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scansione Fattura';

    protected static ?string $title = 'Scansione Fattura';

    protected static ?string $navigationGroup = 'Fatturazione';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.invoice-scan';

    public ?array $data = [];

    public ?Invoice $currentInvoice = null;

    public ?string $scannedUuid = null;

    public ?ProductTwin $scannedProductTwin = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dati Fattura')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->options(Customer::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->currentInvoice = null;
                            }),

                        DatePicker::make('invoice_date')
                            ->label('Data Fattura')
                            ->default(now())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Data Scadenza')
                            ->default(now()->addDays(30))
                            ->required(),

                        Textarea::make('notes')
                            ->label('Note')
                            ->rows(3),
                    ])
                    ->columns(2),

                Section::make('Scansione Prodotti')
                    ->schema([
                        TextInput::make('scanned_uuid')
                            ->label('Scansiona UUID ProductTwin')
                            ->placeholder('Inserisci o scansiona UUID...')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->scannedUuid = $state;
                                $this->scanProductTwin($state);
                            }),

                        // Mostra informazioni del ProductTwin scansionato
                        Section::make('Prodotto Scansionato')
                            ->schema([
                                TextInput::make('product_twin_uuid')
                                    ->label('UUID')
                                    ->disabled()
                                    ->default(fn () => $this->scannedProductTwin?->uuid)
                                    ->visible(fn () => $this->scannedProductTwin !== null),

                                TextInput::make('product_name')
                                    ->label('Prodotto')
                                    ->disabled()
                                    ->default(fn () => $this->scannedProductTwin?->internalProduct?->name)
                                    ->visible(fn () => $this->scannedProductTwin !== null),

                                TextInput::make('warehouse_name')
                                    ->label('Magazzino')
                                    ->disabled()
                                    ->default(fn () => $this->scannedProductTwin?->currentWarehouse?->name ?? 'N/A')
                                    ->visible(fn () => $this->scannedProductTwin !== null),

                                TextInput::make('lifecycle_status')
                                    ->label('Stato')
                                    ->disabled()
                                    ->default(fn () => $this->scannedProductTwin?->lifecycle_status)
                                    ->visible(fn () => $this->scannedProductTwin !== null),
                            ])
                            ->columns(2)
                            ->visible(fn () => $this->scannedProductTwin !== null),
                    ]),

                Section::make('Prodotti Fatturati')
                    ->schema([
                        Repeater::make('items')
                            ->label('Righe Fattura')
                            ->schema([
                                TextInput::make('uuid')
                                    ->label('UUID ProductTwin')
                                    ->disabled(),

                                TextInput::make('product_name')
                                    ->label('Prodotto')
                                    ->disabled(),

                                TextInput::make('unit_price')
                                    ->label('Prezzo Unitario')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Quantità')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                TextInput::make('total_price')
                                    ->label('Totale')
                                    ->disabled(),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('Aggiungi Prodotto')
                            ->visible(fn () => $this->currentInvoice !== null),
                    ])
                    ->visible(fn () => $this->currentInvoice !== null),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create_invoice')
                ->label('Crea Fattura')
                ->action('createInvoice')
                ->visible(fn () => $this->currentInvoice === null && ! empty($this->data['customer_id']))
                ->color('success'),

            Action::make('add_product')
                ->label('Aggiungi Prodotto')
                ->action('addProductToInvoice')
                ->visible(fn () => $this->currentInvoice !== null && $this->scannedProductTwin !== null)
                ->color('primary'),

            Action::make('print_invoice')
                ->label('Stampa Fattura')
                ->action('printInvoice')
                ->visible(fn () => $this->currentInvoice !== null)
                ->color('warning'),

            Action::make('send_electronic')
                ->label('Invia Elettronica')
                ->action('sendElectronicInvoice')
                ->visible(fn () => $this->currentInvoice !== null)
                ->color('info'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_invoice_header')
                ->label('Crea Fattura')
                ->action('createInvoice')
                ->visible(fn () => $this->currentInvoice === null && ! empty($this->data['customer_id']))
                ->color('success'),

            Action::make('add_product_header')
                ->label('Aggiungi Prodotto')
                ->action('addProductToInvoice')
                ->visible(fn () => $this->currentInvoice !== null && $this->scannedProductTwin !== null)
                ->color('primary'),

            Action::make('print_invoice_header')
                ->label('Stampa Fattura')
                ->action('printInvoice')
                ->visible(fn () => $this->currentInvoice !== null)
                ->color('warning'),

            Action::make('send_electronic_header')
                ->label('Invia Elettronica')
                ->action('sendElectronicInvoice')
                ->visible(fn () => $this->currentInvoice !== null)
                ->color('info'),
        ];
    }

    public function scanProductTwin(?string $uuid): void
    {
        if (empty($uuid)) {
            $this->scannedProductTwin = null;
            $this->form->fill();

            return;
        }

        $this->scannedProductTwin = ProductTwin::with(['internalProduct', 'currentWarehouse'])
            ->where('uuid', $uuid)
            ->first();

        if (! $this->scannedProductTwin) {
            Notification::make()
                ->title('UUID non trovato')
                ->body('Il ProductTwin con UUID '.$uuid.' non esiste.')
                ->danger()
                ->send();

            return;
        }

        // Verifica se il ProductTwin è già stato fatturato
        if ($this->scannedProductTwin->invoiceItems()->exists()) {
            Notification::make()
                ->title('Prodotto già fatturato')
                ->body('Questo ProductTwin è già stato fatturato.')
                ->warning()
                ->send();

            return;
        }

        // Verifica se il ProductTwin è disponibile
        if ($this->scannedProductTwin->lifecycle_status !== 'in_stock') {
            Notification::make()
                ->title('Prodotto non disponibile')
                ->body('Il ProductTwin non è disponibile per la fatturazione.')
                ->warning()
                ->send();

            return;
        }

        // Aggiorna il form con i dati del prodotto scansionato
        $this->form->fill([
            'product_twin_uuid' => $this->scannedProductTwin->uuid,
            'product_name' => $this->scannedProductTwin->internalProduct->name,
            'warehouse_name' => $this->scannedProductTwin->currentWarehouse->name ?? 'N/A',
            'lifecycle_status' => $this->scannedProductTwin->lifecycle_status,
        ]);

        Notification::make()
            ->title('Prodotto trovato')
            ->body('ProductTwin scansionato con successo.')
            ->success()
            ->send();
    }

    public function createInvoice(): void
    {
        $this->validate([
            'data.customer_id' => 'required',
            'data.invoice_date' => 'required|date',
            'data.due_date' => 'required|date|after:data.invoice_date',
        ]);

        $this->currentInvoice = Invoice::create([
            'customer_id' => $this->data['customer_id'],
            'invoice_date' => $this->data['invoice_date'],
            'due_date' => $this->data['due_date'],
            'notes' => $this->data['notes'] ?? null,
            'status' => 'draft',
        ]);

        // Ricarica la fattura con le relazioni
        $this->currentInvoice = $this->currentInvoice->fresh(['customer']);

        Notification::make()
            ->title('Fattura creata')
            ->body('Fattura '.$this->currentInvoice->invoice_number.' creata con successo.')
            ->success()
            ->send();
    }

    public function addProductToInvoice(): void
    {
        if (! $this->currentInvoice || ! $this->scannedProductTwin) {
            return;
        }

        // Verifica se il ProductTwin è già nella fattura corrente
        if ($this->currentInvoice->items()->whereHas('productTwins', function ($query) {
            $query->where('product_twin_id', $this->scannedProductTwin->id);
        })->exists()) {
            Notification::make()
                ->title('Prodotto già aggiunto')
                ->body('Questo ProductTwin è già presente nella fattura.')
                ->warning()
                ->send();

            return;
        }

        // Crea la riga fattura
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $this->currentInvoice->id,
            'internal_product_id' => $this->scannedProductTwin->internal_product_id,
            'item_type' => 'physical',
            'quantity' => 1,
            'unit_price' => 0, // Da definire
            'total_price' => 0,
            'description' => $this->scannedProductTwin->internalProduct->name,
        ]);

        // Collega il ProductTwin
        $invoiceItem->productTwins()->attach($this->scannedProductTwin->id);

        // Aggiorna i totali della fattura
        $this->currentInvoice->calculateTotals();

        // Ricarica la fattura per mostrare i dati aggiornati
        $this->currentInvoice = $this->currentInvoice->fresh(['items.productTwins', 'customer']);

        Notification::make()
            ->title('Prodotto aggiunto')
            ->body('ProductTwin aggiunto alla fattura.')
            ->success()
            ->send();

        // Reset del campo di scansione
        $this->scannedUuid = null;
        $this->scannedProductTwin = null;
        $this->data['scanned_uuid'] = null;

        // Ricarica il form
        $this->form->fill();
    }

    public function printInvoice(): void
    {
        if (! $this->currentInvoice) {
            return;
        }

        try {
            // Usa il servizio di stampa
            $printService = app(\App\Services\InvoicePrintService::class);
            $filepath = $printService->print($this->currentInvoice);

            Notification::make()
                ->title('Fattura stampata')
                ->body('PDF generato e salvato: '.$filepath)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore stampa')
                ->body('Errore nella generazione del PDF: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function sendElectronicInvoice(): void
    {
        if (! $this->currentInvoice) {
            Notification::make()
                ->title('Errore')
                ->body('Nessuna fattura selezionata.')
                ->danger()
                ->send();

            return;
        }

        try {
            // Ricarica la fattura con tutte le relazioni necessarie
            $this->currentInvoice->load(['customer', 'items.productTwins', 'items.internalProduct']);

            // Verifica che la fattura abbia tutti i dati necessari
            if (! $this->currentInvoice->customer) {
                Notification::make()
                    ->title('Errore')
                    ->body('Fattura senza cliente associato.')
                    ->danger()
                    ->send();

                return;
            }

            if ($this->currentInvoice->items->isEmpty()) {
                Notification::make()
                    ->title('Errore')
                    ->body('Fattura senza articoli.')
                    ->danger()
                    ->send();

                return;
            }

            // Usa il servizio di fatturazione elettronica
            $electronicService = app(\App\Services\ElectronicInvoiceService::class);
            $success = $electronicService->sendToSdi($this->currentInvoice);

            if ($success) {
                $this->currentInvoice->update(['status' => 'issued']);

                // Ricarica la fattura per mostrare i dati aggiornati
                $this->currentInvoice = $this->currentInvoice->fresh(['customer', 'items.productTwins']);

                Notification::make()
                    ->title('Fattura elettronica inviata')
                    ->body('XML generato e inviato al Sistema di Interscambio.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Errore invio')
                    ->body('Errore nell\'invio della fattura elettronica.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Errore fatturazione elettronica in Filament: '.$e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack trace: '.$e->getTraceAsString());

            Notification::make()
                ->title('Errore fatturazione elettronica')
                ->body('Errore: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
