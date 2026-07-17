<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

/**
 * Seeds a fuller, deterministic demo catalog so `migrate:fresh --seed` gives
 * the storefront a real shop to browse: four brands, a two-level category
 * tree (an "Electronice" root with Telefoane/Căști/Accesorii/Laptopuri as
 * children), two attributes (Culoare, Capacitate) and around a dozen products
 * with priced, stocked variants.
 *
 * Idempotent — keyed on slugs/SKUs, so re-running it won't duplicate rows.
 */
class CatalogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $brands = collect([
            'TechNova' => 'technova',
            'AudioMax' => 'audiomax',
            'Voltra' => 'voltra',
            'NovaBook' => 'novabook',
        ])->mapWithKeys(fn (string $slug, string $name) => [
            $slug => Brand::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]),
        ]);

        $root = Category::firstOrCreate(
            ['slug' => 'electronice'],
            ['name' => 'Electronice', 'is_active' => true],
        );

        // Children of the "Electronice" root, in display order.
        $childCategories = [
            ['name' => 'Telefoane', 'slug' => 'telefoane'],
            ['name' => 'Căști', 'slug' => 'casti'],
            ['name' => 'Accesorii', 'slug' => 'accesorii'],
            ['name' => 'Laptopuri', 'slug' => 'laptopuri'],
        ];

        $categories = collect();

        foreach ($childCategories as $position => $data) {
            $categories[$data['slug']] = Category::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'parent_id' => $root->id, 'position' => $position, 'is_active' => true],
            );
        }

        $culoare = Attribute::firstOrCreate(['slug' => 'culoare'], ['name' => 'Culoare']);
        $capacitate = Attribute::firstOrCreate(['slug' => 'capacitate'], ['name' => 'Capacitate']);

        $values = [
            'negru' => $this->value($culoare, 'Negru', 'negru'),
            'alb' => $this->value($culoare, 'Alb', 'alb'),
            'rosu' => $this->value($culoare, 'Roșu', 'rosu'),
            '128gb' => $this->value($capacitate, '128 GB', '128gb'),
            '256gb' => $this->value($capacitate, '256 GB', '256gb'),
            '512gb' => $this->value($capacitate, '512 GB', '512gb'),
        ];

        $products = [
            [
                'name' => 'Telefon Nova X1', 'slug' => 'telefon-nova-x1', 'brand' => 'technova',
                'category' => 'telefoane', 'price' => 249900, 'description' => 'Flagship compact cu ecran AMOLED.',
                'variants' => [
                    ['sku' => 'NOVA-X1-NEG-128', 'price' => 249900, 'stock' => 12, 'values' => ['negru', '128gb']],
                    ['sku' => 'NOVA-X1-NEG-256', 'price' => 279900, 'stock' => 6, 'values' => ['negru', '256gb']],
                    ['sku' => 'NOVA-X1-ALB-128', 'price' => 249900, 'stock' => 0, 'values' => ['alb', '128gb']],
                ],
            ],
            [
                'name' => 'Telefon Nova Lite', 'slug' => 'telefon-nova-lite', 'brand' => 'technova',
                'category' => 'telefoane', 'price' => 129900, 'description' => 'Autonomie mare, preț accesibil.',
                'variants' => [
                    ['sku' => 'NOVA-LITE-NEG', 'price' => null, 'stock' => 20, 'values' => ['negru']],
                    ['sku' => 'NOVA-LITE-ROS', 'price' => null, 'stock' => 8, 'values' => ['rosu']],
                ],
            ],
            [
                'name' => 'Căști AudioMax Pro', 'slug' => 'casti-audiomax-pro', 'brand' => 'audiomax',
                'category' => 'casti', 'price' => 89900, 'description' => 'Anulare activă a zgomotului.',
                'variants' => [
                    ['sku' => 'AMX-PRO-NEG', 'price' => null, 'stock' => 15, 'values' => ['negru']],
                    ['sku' => 'AMX-PRO-ALB', 'price' => null, 'stock' => 3, 'values' => ['alb']],
                ],
            ],
            [
                'name' => 'Căști AudioMax Air', 'slug' => 'casti-audiomax-air', 'brand' => 'audiomax',
                'category' => 'casti', 'price' => 39900, 'description' => 'Ușoare, in-ear, wireless.',
                'variants' => [
                    ['sku' => 'AMX-AIR-NEG', 'price' => null, 'stock' => 30, 'values' => ['negru']],
                ],
            ],
            [
                'name' => 'Încărcător Voltra 65W', 'slug' => 'incarcator-voltra-65w', 'brand' => 'voltra',
                'category' => 'accesorii', 'price' => 14900, 'description' => 'Încărcare rapidă USB-C.',
                'variants' => [
                    ['sku' => 'VLT-65W-ALB', 'price' => null, 'stock' => 50, 'values' => ['alb']],
                ],
            ],
            [
                'name' => 'Telefon Nova Max', 'slug' => 'telefon-nova-max', 'brand' => 'technova',
                'category' => 'telefoane', 'price' => 349900, 'description' => 'Ecran mare, baterie 5000 mAh.',
                'variants' => [
                    ['sku' => 'NOVA-MAX-NEG-256', 'price' => 349900, 'stock' => 10, 'values' => ['negru', '256gb']],
                    ['sku' => 'NOVA-MAX-ALB-256', 'price' => 349900, 'stock' => 4, 'values' => ['alb', '256gb']],
                ],
            ],
            [
                'name' => 'Căști AudioMax Sport', 'slug' => 'casti-audiomax-sport', 'brand' => 'audiomax',
                'category' => 'casti', 'price' => 59900, 'description' => 'Rezistente la apă, pentru sport.',
                'variants' => [
                    ['sku' => 'AMX-SPORT-NEG', 'price' => null, 'stock' => 18, 'values' => ['negru']],
                ],
            ],
            [
                'name' => 'Laptop NovaBook Slim 14', 'slug' => 'laptop-novabook-slim-14', 'brand' => 'novabook',
                'category' => 'laptopuri', 'price' => 349900, 'description' => 'Ultraportabil, 14", 1.2 kg.',
                'variants' => [
                    ['sku' => 'NBK-SLIM-256', 'price' => 349900, 'stock' => 7, 'values' => ['negru', '256gb']],
                    ['sku' => 'NBK-SLIM-512', 'price' => 419900, 'stock' => 3, 'values' => ['negru', '512gb']],
                ],
            ],
            [
                'name' => 'Laptop NovaBook Pro 16', 'slug' => 'laptop-novabook-pro-16', 'brand' => 'novabook',
                'category' => 'laptopuri', 'price' => 599900, 'description' => 'Pentru muncă grea: 16", GPU dedicat.',
                'variants' => [
                    ['sku' => 'NBK-PRO-512', 'price' => 599900, 'stock' => 5, 'values' => ['negru', '512gb']],
                ],
            ],
            [
                'name' => 'Mouse wireless Voltra', 'slug' => 'mouse-wireless-voltra', 'brand' => 'voltra',
                'category' => 'accesorii', 'price' => 8900, 'description' => 'Silențios, autonomie 12 luni.',
                'variants' => [
                    ['sku' => 'VLT-MOUSE-NEG', 'price' => null, 'stock' => 40, 'values' => ['negru']],
                ],
            ],
            [
                'name' => 'Cablu Voltra USB-C 2m', 'slug' => 'cablu-voltra-usb-c-2m', 'brand' => 'voltra',
                'category' => 'accesorii', 'price' => 4900, 'description' => 'Împletitură textilă, încărcare rapidă.',
                'variants' => [
                    ['sku' => 'VLT-CABLU-ALB', 'price' => null, 'stock' => 60, 'values' => ['alb']],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'brand_id' => $brands[$data['brand']]->id,
                    'price' => $data['price'],
                    'description' => $data['description'],
                    'is_active' => true,
                ],
            );

            $product->categories()->syncWithoutDetaching([$categories[$data['category']]->id]);

            foreach ($data['variants'] as $variantData) {
                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $variantData['sku']],
                    [
                        'product_id' => $product->id,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                    ],
                );

                $variant->attributeValues()->syncWithoutDetaching(
                    collect($variantData['values'])->map(fn (string $slug) => $values[$slug]->id)->all(),
                );
            }
        }
    }

    private function value(Attribute $attribute, string $label, string $slug): AttributeValue
    {
        return AttributeValue::firstOrCreate(
            ['attribute_id' => $attribute->id, 'slug' => $slug],
            ['value' => $label],
        );
    }
}
