<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\PageEditor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pagina')->columns(2)->schema([
                Select::make('locale')->label('Lingua')->options(['it' => 'Italiano', 'en' => 'English'])->required(),
                TextInput::make('slug')->label('Indirizzo (slug)')->required(),
                TextInput::make('title')->label('Titolo')->required(),
                TextInput::make('meta_title')->label('Titolo per Google'),
                TextInput::make('meta_description')->label('Descrizione per Google')->maxLength(320)->columnSpanFull(),
            ]),
            Section::make('Contenuto (trascina i blocchi)')->schema([
                PageEditor::make('html')->label('')->columnSpanFull(),
                Hidden::make('css'),
            ]),
        ]);
    }
}
