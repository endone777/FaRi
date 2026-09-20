<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $contact
 * @property string $topic
 * @property string $message
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'contact', 'topic', 'message', 'status'])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'new' => 'Новое',
        'answered' => 'Отвечено',
        'closed' => 'Закрыто',
    ];

    /**
     * Subjects offered on the public contact form.
     *
     * @var list<string>
     */
    public const TOPICS = [
        'Подбор по VIN',
        'Наличие и сроки',
        'Гарантия и возврат',
        'Доставка в регион',
        'Уведомить о поступлении',
        'Отзыв о товаре',
        'Другое',
    ];
}
