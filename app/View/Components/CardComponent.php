<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $title;
    public $content;
    public $imgUrl;
    public function __construct($title, $content, $imgUrl = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->imgUrl = $imgUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.card-component');
    }
}
