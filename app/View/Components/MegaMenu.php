<?php

namespace App\View\Components;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class MegaMenu extends Component
{
    public Collection $categories;

    public function __construct(public string $locale)
    {
        $this->categories = Category::with('translations')->orderBy('sort')->get();
    }

    public function render(): View
    {
        return view('components.mega-menu');
    }
}
