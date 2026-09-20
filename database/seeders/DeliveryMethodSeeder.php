<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $methods = [
            [
                'code' => 'pickup', 'name' => 'Самовывоз со склада', 'cost' => 0, 'days' => 'в день заказа',
                'note' => 'Москва, ул. Автомоторная, 4с1. Заказ держим три дня, при вас проверяем фару на стенде и светим на экран.',
                'free_from' => null, 'position' => 1,
            ],
            [
                'code' => 'msk', 'name' => 'Курьер по Москве в пределах МКАД', 'cost' => 600, 'days' => '1–2 дня',
                'note' => 'Бесплатно при заказе от 50 000 ₽. Курьер ждёт 15 минут, пока вы осматриваете стекло и корпус.',
                'free_from' => 50000, 'position' => 2,
            ],
            [
                'code' => 'mo', 'name' => 'Московская область за МКАД', 'cost' => 600, 'days' => '1–3 дня',
                'note' => '600 ₽ плюс 35 ₽ за километр от МКАД. Точную сумму называем до отправки, сюрпризов при получении нет.',
                'free_from' => null, 'position' => 3,
            ],
            [
                'code' => 'tk', 'name' => 'Транспортная компания в регионы', 'cost' => 900, 'days' => '3–9 дней',
                'note' => 'СДЭК, Деловые Линии, ПЭК. Считается по весу и габаритам: фара в упаковке — это объёмное место 60×40×35 см.',
                'free_from' => null, 'position' => 4,
            ],
            [
                'code' => 'post', 'name' => 'Почта России', 'cost' => 450, 'days' => '5–14 дней',
                'note' => 'Только для мелких позиций до 3 кг: лампы, блоки розжига, крепёж. Фары в сборе почтой не отправляем.',
                'free_from' => null, 'position' => 5,
            ],
        ];

        foreach ($methods as $method) {
            DeliveryMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
