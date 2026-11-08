<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Redirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadCatalog extends Command
{
    protected $signature = 'site:load-catalog {path=database/catalog.json}';

    protected $description = 'Carica categorie e prodotti da un file JSON creato con site:export-catalog (idempotente)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("File non trovato: $path");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || ! isset($data['categories'])) {
            $this->error('File non valido: non è un esportazione del catalogo.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($data) {
            foreach ($data['categories'] as $c) {
                $category = Category::updateOrCreate(['slug' => $c['slug']], ['sort' => $c['sort'], 'image_path' => $c['image_path']]);
                foreach ($c['translations'] as $t) {
                    $category->translations()->updateOrCreate(['locale' => $t['locale']], ['name' => $t['name'], 'slug' => $t['slug']]);
                }
                foreach ($c['products'] as $p) {
                    $product = Product::updateOrCreate(['code' => $p['code']], [
                        'category_id' => $category->id, 'weight_g' => $p['weight_g'], 'length_cm' => $p['length_cm'],
                        'link_mm' => $p['link_mm'], 'titles' => $p['titles'],
                    ]);
                    foreach ($p['translations'] as $t) {
                        $product->translations()->updateOrCreate(['locale' => $t['locale']], ['name' => $t['name'], 'slug' => $t['slug'], 'description' => $t['description'] ?? null]);
                    }
                    foreach ($p['images'] as $i) {
                        $product->images()->updateOrCreate(['path' => $i['path']], ['sort' => $i['sort']]);
                    }
                }
            }
            foreach ($data['redirects'] ?? [] as $r) {
                Redirect::updateOrCreate(['from_path' => Redirect::normalize($r['from_path'])], ['to_path' => $r['to_path']]);
            }
        });

        $this->info(Product::count().' prodotti, '.Category::count().' categorie nel catalogo');

        return self::SUCCESS;
    }
}
