<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Field;

/** Editor libero trascina-e-rilascia (GrapesJS) per le pagine istituzionali. */
class PageEditor extends Field
{
    protected string $view = 'filament.forms.page-editor';

    protected string $cssStatePath = 'data.css';

    public function cssStatePath(string $path): static
    {
        $this->cssStatePath = $path;

        return $this;
    }

    public function getCssStatePath(): string
    {
        return $this->cssStatePath;
    }
}
