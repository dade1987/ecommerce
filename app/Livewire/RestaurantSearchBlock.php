<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\Address;
use Livewire\Component;
use Filament\Forms\Form;
use App\Models\Restaurant;
use Filament\Forms\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;

class RestaurantSearchBlock extends Component implements HasForms
{
    use InteractsWithForms;

    public string $search = '';
    public string $search_address = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 10; // Raggio di default in km
    public $showCreateModal = false;
    public ?string $googleMapsApiKey;

    public ?array $formData = [];

    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->makeForm()),
            'addressForm' => $this->addressForm($this->makeForm()),
        ];
    }

    public function mount(): void
    {
        $this->googleMapsApiKey = config('services.google.maps.api_key');
        $this->search = request()->query('search', $this->search);
        $this->search_address = request()->query('search_address', $this->search_address);
        $this->latitude = request()->query('latitude', $this->latitude);
        $this->longitude = request()->query('longitude', $this->longitude);
        $this->radius = request()->query('radius', $this->radius);
        $this->form->fill();
        $this->addressForm->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
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

    public function addressForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('address_search')
                ->label('Cerca Indirizzo')
                ->placeholder('Inizia a digitare un indirizzo...')
                ->extraInputAttributes(['id' => 'modal_address_search'])
                ->columnSpanFull(),
            Forms\Components\TextInput::make('street')->label('Via')->required(),
            Forms\Components\TextInput::make('municipality')->label('Comune')->required(),
            Forms\Components\TextInput::make('province')->label('Provincia')->required(),
            Forms\Components\TextInput::make('postal_code')->label('CAP')->required(),
            Forms\Components\TextInput::make('region')->label('Regione')->required(),
            Forms\Components\TextInput::make('nation')->label('Nazione')->required(),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('latitude')->required(),
                Forms\Components\TextInput::make('longitude')->required(),
            ]),
        ])->statePath('formData');
    }

    public function salvaRistorante()
    {
        $data = $this->form->getState();
        $addressDataFromForm = $this->addressForm->getState();

        $restaurantData = [
            'name' => $data['name'],
            'price_range' => $data['price_range'],
            'phone_number' => $data['phone_number'],
            'website' => $data['website'],
            'email' => $data['email'],
        ];

        $addressData = [
            'street' => $addressDataFromForm['street'],
            'nation' => $addressDataFromForm['nation'],
            'region' => $addressDataFromForm['region'],
            'province' => $addressDataFromForm['province'],
            'municipality' => $addressDataFromForm['municipality'],
            'postal_code' => $addressDataFromForm['postal_code'],
            'latitude' => $addressDataFromForm['latitude'],
            'longitude' => $addressDataFromForm['longitude'],
        ];

        $restaurant = Restaurant::create($restaurantData);
        $address = Address::create($addressData);

        $restaurant->addresses()->attach($address->id);

        $this->closeCreateModal();
        $this->form->fill();
        $this->addressForm->fill();

        Notification::make()
            ->title('Ristorante creato con successo')
            ->success()
            ->send();
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->dispatch('open-create-modal');
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
