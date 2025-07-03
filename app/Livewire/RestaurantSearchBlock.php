<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\Address;
use Livewire\Component;
use Filament\Forms\Form;
use App\Models\Restaurant;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use function Safe\date;

class RestaurantSearchBlock extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public string $search = '';
    public string $search_address = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 10; // Raggio di default in km
    public ?string $googleMapsApiKey;
    public string $date;
    public string $time_slot;

    public function mount(): void
    {
        $this->googleMapsApiKey = config('services.google.maps.api_key');
        $this->search = request()->query('search', $this->search);
        $this->search_address = request()->query('search_address', $this->search_address);
        $this->latitude = request()->query('latitude', $this->latitude);
        $this->longitude = request()->query('longitude', $this->longitude);
        $this->radius = request()->query('radius', $this->radius);
        $this->date = request()->query('date', date('Y-m-d'));
        $this->time_slot = request()->query('time_slot', '19:00');
    }

    public function addRestaurantAction(): Action
    {
        return Action::make('addRestaurant')
            ->label('Aggiungi Ristorante')
            ->form([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Section::make('Dati Ristorante')->schema([
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
                    ])->columnSpan(1),
                    Forms\Components\Section::make('Indirizzo')->schema([
                        Forms\Components\TextInput::make('address_search')
                            ->label('Cerca Indirizzo')
                            ->placeholder('Inizia a digitare un indirizzo...')
                            ->extraInputAttributes(['id' => 'modal_address_search']),
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
                    ])->columnSpan(1),
                ]),
            ])
            ->action(function (array $data) {
                $restaurantData = [
                    'name' => $data['name'],
                    'price_range' => $data['price_range'],
                    'phone_number' => $data['phone_number'],
                    'website' => $data['website'],
                    'email' => $data['email'],
                ];

                $addressData = [
                    'street' => $data['street'],
                    'nation' => $data['nation'],
                    'region' => $data['region'],
                    'province' => $data['province'],
                    'municipality' => $data['municipality'],
                    'postal_code' => $data['postal_code'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ];

                $restaurant = Restaurant::create($restaurantData);
                $address = Address::create($addressData);

                $restaurant->addresses()->attach($address->id);

                Notification::make()
                    ->title('Ristorante creato con successo')
                    ->success()
                    ->send();
            })
            ->modalWidth('4xl')
            ->slideOver();
    }

    public function render()
    {
        return view('livewire.restaurant-search-block');
    }
}
