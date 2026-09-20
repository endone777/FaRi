<?php

namespace Database\Seeders;

use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Generations sold from 2010 onwards for the four makes the shop carries.
 * A null second year means the generation is still in production.
 */
class CarDirectorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $position = 0;

        foreach ($this->directory() as $brandName => $models) {
            $brand = CarBrand::updateOrCreate(
                ['name' => $brandName],
                ['slug' => Str::slug($brandName), 'position' => ++$position],
            );

            foreach ($models as $name => $years) {
                CarModel::updateOrCreate(
                    ['car_brand_id' => $brand->id, 'name' => $name],
                    [
                        'slug' => Str::slug($name),
                        'year_from' => $years[0],
                        'year_to' => $years[1],
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, array<string, array{int, int|null}>>
     */
    private function directory(): array
    {
        return [
            'BMW' => [
                '1 Series (F20)' => [2011, 2019],
                '1 Series (F40)' => [2019, null],
                '2 Series (F22)' => [2013, 2021],
                '2 Series (G42)' => [2021, null],
                '3 Series (F30)' => [2011, 2019],
                '3 Series (G20)' => [2018, null],
                '4 Series (F32)' => [2013, 2020],
                '4 Series (G22)' => [2020, null],
                '5 Series (F10)' => [2010, 2017],
                '5 Series (G30)' => [2016, 2023],
                '5 Series (G60)' => [2023, null],
                '6 Series (F12)' => [2011, 2018],
                '7 Series (F01)' => [2010, 2015],
                '7 Series (G11)' => [2015, 2022],
                '7 Series (G70)' => [2022, null],
                '8 Series (G15)' => [2018, null],
                'X1 (E84)' => [2010, 2015],
                'X1 (F48)' => [2015, 2022],
                'X1 (U11)' => [2022, null],
                'X2 (F39)' => [2018, 2023],
                'X3 (F25)' => [2010, 2017],
                'X3 (G01)' => [2017, null],
                'X4 (F26)' => [2014, 2018],
                'X4 (G02)' => [2018, null],
                'X5 (F15)' => [2013, 2018],
                'X5 (G05)' => [2018, null],
                'X6 (F16)' => [2014, 2019],
                'X6 (G06)' => [2019, null],
                'X7 (G07)' => [2018, null],
                'Z4 (E89)' => [2010, 2016],
                'Z4 (G29)' => [2018, null],
                'i3 (I01)' => [2013, 2022],
                'i4 (G26)' => [2021, null],
                'iX (I20)' => [2021, null],
            ],
            'Audi' => [
                'A1 (8X)' => [2010, 2018],
                'A1 (GB)' => [2018, null],
                'A3 (8P)' => [2010, 2013],
                'A3 (8V)' => [2012, 2020],
                'A3 (8Y)' => [2020, null],
                'A4 (B8)' => [2010, 2015],
                'A4 (B9)' => [2015, null],
                'A5 (8T)' => [2010, 2016],
                'A5 (F5)' => [2016, null],
                'A6 (C7)' => [2011, 2018],
                'A6 (C8)' => [2018, null],
                'A7 (4G)' => [2010, 2018],
                'A7 (4K)' => [2018, null],
                'A8 (D4)' => [2010, 2017],
                'A8 (D5)' => [2017, null],
                'Q2 (GA)' => [2016, null],
                'Q3 (8U)' => [2011, 2018],
                'Q3 (F3)' => [2018, null],
                'Q5 (8R)' => [2010, 2016],
                'Q5 (FY)' => [2016, null],
                'Q7 (4L)' => [2010, 2015],
                'Q7 (4M)' => [2015, null],
                'Q8 (4M)' => [2018, null],
                'TT (8J)' => [2010, 2014],
                'TT (8S)' => [2014, 2023],
                'e-tron (GE)' => [2018, null],
            ],
            'Volkswagen' => [
                'Polo (6R)' => [2010, 2017],
                'Polo (AW)' => [2017, null],
                'Golf VI' => [2010, 2012],
                'Golf VII' => [2012, 2020],
                'Golf VIII' => [2019, null],
                'Jetta (A6)' => [2010, 2018],
                'Jetta (A7)' => [2018, null],
                'Passat B7' => [2010, 2015],
                'Passat B8' => [2014, 2022],
                'Passat B9' => [2023, null],
                'Arteon' => [2017, null],
                'Tiguan I' => [2010, 2016],
                'Tiguan II' => [2016, 2023],
                'Tiguan III' => [2023, null],
                'Touareg (NF)' => [2010, 2018],
                'Touareg (CR)' => [2018, null],
                'Touran (5T)' => [2015, null],
                'Caddy (2K)' => [2010, 2020],
                'Caddy (SB)' => [2020, null],
                'Transporter T5' => [2010, 2015],
                'Transporter T6' => [2015, null],
                'Teramont' => [2017, null],
                'ID.4' => [2020, null],
            ],
            'Toyota' => [
                'Corolla (E150)' => [2010, 2013],
                'Corolla (E170)' => [2013, 2018],
                'Corolla (E210)' => [2018, null],
                'Camry XV40' => [2010, 2011],
                'Camry XV50' => [2011, 2017],
                'Camry XV70' => [2017, null],
                'RAV4 XA30' => [2010, 2012],
                'RAV4 XA40' => [2012, 2018],
                'RAV4 XA50' => [2018, null],
                'Land Cruiser 200' => [2010, 2021],
                'Land Cruiser 300' => [2021, null],
                'Land Cruiser Prado 150' => [2010, null],
                'Highlander XU40' => [2010, 2013],
                'Highlander XU50' => [2013, 2019],
                'Highlander XU70' => [2019, null],
                'Hilux (AN20)' => [2010, 2015],
                'Hilux (AN120)' => [2015, null],
                'Yaris (XP130)' => [2011, 2020],
                'Yaris (XP210)' => [2020, null],
                'Avensis (T270)' => [2010, 2018],
                'C-HR (AX10)' => [2016, 2023],
                'C-HR (AX20)' => [2023, null],
                'Prius XW30' => [2010, 2015],
                'Prius XW50' => [2015, 2022],
                'Prius XW60' => [2022, null],
            ],
        ];
    }
}
