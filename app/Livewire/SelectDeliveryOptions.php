<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use App\Notifications\CustomerOrder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class SelectDeliveryOptions extends Component implements HasForms
{
    use InteractsWithForms;
    public ?array $addressData = [];
    public ?array $dateData = [];
    public User $user;

    private array $items = [];
    private string $total = '';

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->selectAddressForm->fill();
        $this->selectDateForm->fill();
    }

    public function selectAddressForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('address_id')->relationship(name: 'addresses', titleAttribute: 'street')
            ])
            ->statePath('addressData')
            ->model($this->user);
    }

    public function selectDateForm(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('delivery_date')
                    ->native(false)
                    ->seconds(false)
                    ->hoursStep(1)
                    ->minutesStep(15)

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
        $this->items = Session::get('cart') ?? [];
        $this->total = number_format(array_reduce($this->items, fn ($carry, $item) => $carry += $item['price'], 0), 2);


        $order = $this->user->orders()->create(['delivery_date' => $this->selectDateForm->getState()['delivery_date']]);
        foreach ($this->items as $item) {
            $order->products()->attach($item);
        }
        $order->addresses()->attach($this->selectAddressForm->getState()['address_id']);
        $order->save();

        Session::remove('cart');

        Notification::route('mail', [
            'davidecavallini1987@gmail.com' => 'Davide Cavallini',
        ])->notify(new CustomerOrder($order, $this->total));

        return redirect()->route('{item2?}.index', ['container0' => 'order-completed']);
    }

    public function render(): View
    {
        return view('livewire.select-delivery-options');
    }
}
