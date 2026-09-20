<?php

namespace Database\Seeders;

use App\Models\CarModel;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ($this->products() as $product) {
            $carModel = CarModel::query()
                ->whereRelation('carBrand', 'name', $product['brand'])
                ->where('name', $product['model'])
                ->first();

            if ($carModel === null) {
                throw new RuntimeException(
                    "Нет в справочнике автомобилей: {$product['brand']} {$product['model']}",
                );
            }

            unset($product['brand'], $product['model']);
            $product['car_model_id'] = $carModel->id;

            Product::updateOrCreate(['slug' => $product['slug']], $product);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(): array
    {
        return [
            [
                'slug' => 'bmw-3-g20-adaptive-led',
                'brand' => 'BMW', 'model' => '3 Series (G20)', 'year_from' => 2018, 'year_to' => 2022,
                'name' => 'Adaptive LED в сборе', 'tech' => 'LED', 'color_temp' => 5000,
                'oem' => 'DEMO-63-11-8-092-471', 'shape' => 'sharp', 'price' => 124900,
                'qty' => 4, 'stock_note' => 'на складе', 'weight' => 5.4, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/bm1.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Фара в сборе с адаптивным дальним светом и поворотным модулем. Корпус без трещин, стекло без желтизны и пескоструя, все крепёжные уши целые. Диодные модули и блок розжига проверены на стенде.',
            ],
            [
                'slug' => 'bmw-5-g30-bi-xenon',
                'brand' => 'BMW', 'model' => '5 Series (G30)', 'year_from' => 2017, 'year_to' => 2020,
                'name' => 'Bi-Xenon в сборе', 'tech' => 'Ксенон', 'color_temp' => 4300,
                'oem' => 'DEMO-63-11-7-214-963', 'shape' => 'sharp', 'price' => 78500,
                'qty' => 0, 'stock_note' => 'под заказ, 5–7 дней', 'weight' => 5.1, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/bm2.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Биксеноновая фара с линзой и шторкой ближнего-дальнего. Комплектуется рабочей лампой D1S и блоком розжига. Регулировочные винты вращаются свободно, механизм корректора исправен.',
            ],
            [
                'slug' => 'bmw-x5-g05-laserlight',
                'brand' => 'BMW', 'model' => 'X5 (G05)', 'year_from' => 2018, 'year_to' => 2023,
                'name' => 'Laserlight в сборе', 'tech' => 'LED', 'color_temp' => 5500,
                'oem' => 'DEMO-63-11-8-089-104', 'shape' => 'sharp', 'price' => 289000,
                'qty' => 2, 'stock_note' => 'на складе', 'weight' => 6.8, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/bm3.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Топовая оптика с лазерным модулем дальнего света и матричной секцией. Дальность луча заметно выше светодиодной версии. Устанавливается только на машины, где лазерный свет предусмотрен проводкой.',
            ],
            [
                'slug' => 'audi-a4-b9-matrix-led',
                'brand' => 'Audi', 'model' => 'A4 (B9)', 'year_from' => 2015, 'year_to' => 2019,
                'name' => 'Matrix LED в сборе', 'tech' => 'LED', 'color_temp' => 5300,
                'oem' => 'DEMO-8W0-941-035-A', 'shape' => 'sharp', 'price' => 156000,
                'qty' => 3, 'stock_note' => 'на складе', 'weight' => 5.6, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/au1.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Матричная фара с посегментным отключением диодов и динамическим поворотником. Плата управления и шлейфы целые, разъёмы без окисления. Требует кодирования при установке.',
            ],
            [
                'slug' => 'audi-q5-fy-full-led',
                'brand' => 'Audi', 'model' => 'Q5 (FY)', 'year_from' => 2017, 'year_to' => 2020,
                'name' => 'Full LED в сборе', 'tech' => 'LED', 'color_temp' => 5000,
                'oem' => 'DEMO-80A-941-035-C', 'shape' => 'swept', 'price' => 112000,
                'qty' => 5, 'stock_note' => 'на складе', 'weight' => 5.9, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/au2.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Светодиодная фара в сборе с дневными ходовыми огнями и бегущим поворотником. Стекло чистое, внутри нет запотевания и следов разборки. Сапуны на месте.',
            ],
            [
                'slug' => 'audi-a6-c7-bi-xenon',
                'brand' => 'Audi', 'model' => 'A6 (C7)', 'year_from' => 2011, 'year_to' => 2015,
                'name' => 'Bi-Xenon в сборе', 'tech' => 'Ксенон', 'color_temp' => 4300,
                'oem' => 'DEMO-4G0-941-003-B', 'shape' => 'swept', 'price' => 94000,
                'qty' => 0, 'stock_note' => 'под заказ, 7–10 дней', 'weight' => 5.2, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/au3.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Биксенон со светодиодной полосой ходовых огней. Все сегменты полосы светят ровно, без выпавших диодов и разнооттеночности. Корпус без сварки и пайки.',
            ],
            [
                'slug' => 'vw-golf-vii-bi-xenon',
                'brand' => 'Volkswagen', 'model' => 'Golf VII', 'year_from' => 2012, 'year_to' => 2016,
                'name' => 'Bi-Xenon в сборе', 'tech' => 'Ксенон', 'color_temp' => 4300,
                'oem' => 'DEMO-5G1-941-033-B', 'shape' => 'slim', 'price' => 62000,
                'qty' => 6, 'stock_note' => 'на складе', 'weight' => 4.3, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/vw1.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Ксеноновая фара с линзой и светодиодными ходовыми огнями. Популярная замена родной галогенной оптике: посадочные места и разъёмы совпадают, но потребуется блок розжига и корректор.',
            ],
            [
                'slug' => 'vw-tiguan-ii-led',
                'brand' => 'Volkswagen', 'model' => 'Tiguan II', 'year_from' => 2016, 'year_to' => 2020,
                'name' => 'LED в сборе', 'tech' => 'LED', 'color_temp' => 5000,
                'oem' => 'DEMO-5NA-941-035-A', 'shape' => 'slim', 'price' => 71500,
                'qty' => 4, 'stock_note' => 'на складе', 'weight' => 4.8, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/vw2.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Светодиодная фара в сборе, ближний и дальний на диодах. Рассеиватель без потёртостей, отражатели зеркальные. Встаёт на штатные кронштейны без доработок.',
            ],
            [
                'slug' => 'vw-passat-b8-halogen',
                'brand' => 'Volkswagen', 'model' => 'Passat B8', 'year_from' => 2014, 'year_to' => 2019,
                'name' => 'Галоген в сборе', 'tech' => 'Галоген', 'color_temp' => 3200,
                'oem' => 'DEMO-3G1-941-005-C', 'shape' => 'slim', 'price' => 34900,
                'qty' => 9, 'stock_note' => 'на складе', 'weight' => 3.9, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/vw3.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Базовая галогенная фара в сборе — бюджетный вариант после удара или помутнения стекла. Лампы H7 и W5W в комплект не входят, ставятся любые из наличия.',
            ],
            [
                'slug' => 'toyota-camry-xv70-led',
                'brand' => 'Toyota', 'model' => 'Camry XV70', 'year_from' => 2017, 'year_to' => 2021,
                'name' => 'LED в сборе', 'tech' => 'LED', 'color_temp' => 5000,
                'oem' => 'DEMO-81150-06D40', 'shape' => 'swept', 'price' => 68000,
                'qty' => 7, 'stock_note' => 'на складе', 'weight' => 4.6, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/ty1.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Светодиодная фара в сборе для рестайлинговой Камри. Внутренние элементы без пожелтения, герметик по периметру родной. Проверена на плотность луча и границу светотени.',
            ],
            [
                'slug' => 'toyota-rav4-xa50-led',
                'brand' => 'Toyota', 'model' => 'RAV4 XA50', 'year_from' => 2018, 'year_to' => 2023,
                'name' => 'LED в сборе', 'tech' => 'LED', 'color_temp' => 5200,
                'oem' => 'DEMO-81150-42K10', 'shape' => 'swept', 'price' => 74900,
                'qty' => 5, 'stock_note' => 'на складе', 'weight' => 4.9, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/ty2.jpg', 'photo_old_path' => 'images/catalog/old1.jpg',
                'description' => 'Фара в сборе со светодиодным ближним и отдельной секцией ходовых огней. Корпус и стекло без дефектов, кронштейны не клееные — частая проблема разборных фар.',
            ],
            [
                'slug' => 'toyota-lc200-bi-led',
                'brand' => 'Toyota', 'model' => 'Land Cruiser 200', 'year_from' => 2015, 'year_to' => 2021,
                'name' => 'Bi-LED в сборе', 'tech' => 'LED', 'color_temp' => 5000,
                'oem' => 'DEMO-81145-60K20', 'shape' => 'sharp', 'price' => 138000,
                'qty' => 0, 'stock_note' => 'под заказ, 10–14 дней', 'weight' => 6.2, 'warranty_months' => 12,
                'photo_path' => 'images/catalog/ty3.jpg', 'photo_old_path' => 'images/catalog/old2.jpg',
                'description' => 'Фара в сборе с двухрежимным светодиодным модулем для рестайлинга 200-го кузова. Тяжёлая, отправляется в жёстком коробе с пенопластовым ложементом.',
            ],
        ];
    }
}
