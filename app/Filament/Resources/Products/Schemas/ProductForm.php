<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dati prodotto')->columns(2)->schema([
                TextInput::make('code')->label('Codice articolo')->required()->unique(ignoreRecord: true),
                Select::make('category_id')->label('Categoria')->relationship('category', 'slug')->required(),
                TextInput::make('weight_g')->label('Peso (g)')->numeric(),
                TextInput::make('length_cm')->label('Dimensioni (cm)')->numeric(),
                TextInput::make('link_mm')->label('Misura maglia (mm)')->numeric(),
                TagsInput::make('titles')->label('Titoli disponibili (es. AG, AU 750, AU 585, AU 375)'),
            ]),
            Section::make('Testi (italiano e inglese affiancati)')->schema([
                Repeater::make('translations')->label('')->relationship()
                    ->grid(2)->addable(false)->deletable(false)->reorderable(false)
                    ->default([['locale' => 'it'], ['locale' => 'en']])
                    ->schema([
                        Select::make('locale')->label('Lingua')->options(['it' => 'Italiano', 'en' => 'English'])->disabled()->dehydrated()->required(),
                        TextInput::make('name')->label('Nome')->required(),
                        TextInput::make('slug')->label('Indirizzo (slug)')->required(),
                        Textarea::make('description')->label('Descrizione')->rows(4),
                    ]),
            ]),
            Section::make('Immagini')->schema([
                Repeater::make('images')->label('')->relationship()->orderColumn('sort')
                    ->schema([FileUpload::make('path')->label('Immagine')->image()->directory('products')->required()]),
            ]),
        ]);
    }
}
