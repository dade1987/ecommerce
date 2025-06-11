<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Notifications\Notification;

class RestaurantSearchBlock extends Component implements HasForms
{
    use InteractsWithForms;

    public string $search = '';
    public $showCreateModal = false;

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill($this->formData);
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            /*CuratorPicker::make('feature_image_id')
                ->label('Immagine di copertina')
                ->required(),*/
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('price_range')
                ->label('Fascia prezzo')
                ->options([
                    'E' => '10-20 €',
                    'EE' => '20-40 €',
                    'EEE' => '40-50 €',
                    'EEEE' => '50-100 €',
                ])
                ->required(),
            Forms\Components\TextInput::make('phone_number')
                ->label('Telefono')
                ->tel()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('website')
                ->label('Sito web')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),
        ])->statePath('formData');
    }

    public function salvaRistorante()
    {
        $data = $this->form->getState();

        Restaurant::create($data);
        $this->closeCreateModal();
        $this->form->fill(); // resetta il form
        $this->dispatch('filterRestaurants', search: $this->search); // aggiorna la lista

        Notification::make()
            ->title('Ristorante creato con successo')
            ->success()
            ->send();
    }

    public function cerca()
    {
        $this->js("window.history.replaceState({}, '', '?search=" . urlencode($this->search) . "')");
        $this->dispatch('filterRestaurants', search: $this->search);
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        return view('livewire.restaurant-search-block');
    }
}
