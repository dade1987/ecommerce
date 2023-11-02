<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ThankYou extends Component
{
    public string $title;
    public string $subtitle;
    public string $button_text;
    public string $button_link;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title, string $subtitle, string $button_text, string $button_link)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->button_text =  $button_text;
        $this->button_link = $button_link;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.thank-you', ['title' => $this->title, 'subtitle' => $this->subtitle, 'button_text' => $this->button_text, 'button_link' => $this->button_link]);
    }
}
