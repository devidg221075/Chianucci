<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Codice')->searchable()->sortable(),
                TextColumn::make('category.slug')->label('Categoria')->sortable(),
                TextColumn::make('weight_g')->label('Peso (g)'),
                TextColumn::make('length_cm')->label('Dimensioni (cm)'),
                TextColumn::make('link_mm')->label('Maglia (mm)'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
