<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Redirect;
use Illuminate\Console\Command;

class ExportCatalog extends Command
{
    protected $signature = 'site:export-catalog {path=database/catalog.json}';

    protected $description = 'Esporta categorie e prodotti in un file JSON (senza prezzi) da caricare sul server';

    public function handle(): int
    {
        $categories = Category::with(['translations', 'products.translations', 'products.images'])->orderBy('sort')->get()->map(fn ($c) => [
            'slug' => $c->slug,
            'sort' => $c->sort,
            'image_path' => $c->image_path,
            'translations' => $c->translations->map->only(['locale', 'name', 'slug'])->values()->all(),
            'products' => $c->products->map(fn ($p) => [
                'code' => $p->code,
                'weight_g' => $p->weight_g,
                'length_cm' => $p->length_cm,
                'link_mm' => $p->link_mm,
                'titles' => $p->titles ?? [],
                'translations' => $p->translations->map->only(['locale', 'name', 'slug', 'description'])->values()->all(),
                'images' => $p->images->map->only(['path', 'sort'])->values()->all(),
            ])->values()->all(),
        ])->values()->all();

        $path = $this->argument('path');
        file_put_contents($path, json_encode(['version' => 1, 'categories' => $categories, 'redirects' => Redirect::orderBy('id')->get(['from_path', 'to_path'])->all()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info(count($categories).' categorie esportate in '.$path);

        return self::SUCCESS;
    }
}
