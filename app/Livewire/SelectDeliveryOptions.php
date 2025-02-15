<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\User;
use App\Notifications\CustomerOrder;
use App\Services\Cart\Facades\Cart;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class SelectDeliveryOptions extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $addressData = [];

    public ?array $dateData = [];

    public User $user;

    private array $items = [];

    private string $total = '';

    public function mount(): void
    {
        $this->user = Auth::user();
        /** @phpstan-ignore-next-line */
        $this->selectAddressForm->fill();
        /** @phpstan-ignore-next-line */
        $this->selectDateForm->fill();
    }

    public function addressAction(): Action
    {

        return Action::make('address')
            ->label('Add Address')
            ->form([
                TextInput::make('nation')->required(),
                TextInput::make('region')->required(),
                TextInput::make('province')->required(),
                TextInput::make('municipality')->required(),
                TextInput::make('street')->required(),
                TextInput::make('postal_code')->required(),
            ])
            //->requiresConfirmation()
            ->action(fn (array $data) => Auth::user()->addresses()->create($data));
    }

    public function selectAddressForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('address_id')
                    ->label('Indirizzo')
                    ->relationship(name: 'addresses'/*, titleAttribute: 'street'*/)
                    ->getOptionLabelFromRecordUsing(fn (Address $record) => $record->full_address),
            ])
            ->statePath('addressData')
            ->model($this->user);
    }

    public function selectDateForm(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('delivery_date')
                    ->label('Data di consegna desiderata')
                    ->native(false)
                    ->seconds(false)
                    ->default(now())
                    ->hoursStep(1)
                    ->minutesStep(15),

            ])
            ->statePath('dateData');
    }

    protected function getForms(): array
    {
        return [
            'selectDateForm',
            'selectAddressForm',
        ];
    }

    public function sendOrder()
    {
        $this->items = Cart::content()->flatMap(function ($item) {
            // Estrarre gli elementi subitems all'interno di un array
            $subitems = $item->subItems->toArray();

            // Unire gli item principali e i subitems in un unico array
            return array_merge([$item], $subitems);
        })->values()->toArray();

        $this->total = number_format(Cart::total(), 2);

        /** @phpstan-ignore-next-line */
        $order = $this->user->orders()->create(['delivery_date' => $this->selectDateForm->getState()['delivery_date']]);
        foreach ($this->items as $item) {
            //dd(app($item['options']['model_type'])->find($item['options']['model_id']));
            $order->products()->attach(app($item['options']['model_type'])->find($item['options']['model_id']));
        }

        /** @phpstan-ignore-next-line */
        $order->addresses()->attach($this->selectAddressForm->getState()['address_id']);
        $order->save();

        Cart::destroy();

        Notification::route('mail', [
            'd.cavallini@cavalliniservice.com' => 'Davide Cavallini',
            'g.florian@cavalliniservice.com' => 'Giuliano Florian',
        ])->notify(new CustomerOrder($order, $this->total));

        return redirect()->route('{item2?}.index', ['container0' => 'order-completed']);
    }

    public function render(): View
    {
        return view('livewire.select-delivery-options');
    }
}
