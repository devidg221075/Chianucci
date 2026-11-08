<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Categoria')->columns(2)->schema([
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('sort')->label('Ordine')->numeric()->default(0),
                FileUpload::make('image_path')->label('Foto per il mega menu')->image()->directory('categories'),
            ]),
            Section::make('Nome (italiano e inglese affiancati)')->schema([
                Repeater::make('translations')->label('')->relationship()
                    ->grid(2)->addable(false)->deletable(false)->reorderable(false)
                    ->default([['locale' => 'it'], ['locale' => 'en']])
                    ->schema([
                        Select::make('locale')->label('Lingua')->options(['it' => 'Italiano', 'en' => 'English'])->disabled()->dehydrated()->required(),
                        TextInput::make('name')->label('Nome')->required(),
                        TextInput::make('slug')->label('Indirizzo (slug)')->required(),
                    ]),
            ]),
        ]);
    }
}
