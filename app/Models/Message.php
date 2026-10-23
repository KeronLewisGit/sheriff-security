<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * General contact-form message.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property string $topic
 * @property string $body
 * @property Carbon|null $handled_at
 * @property Carbon $created_at
 */
class Message extends Model
{
    public const array TOPICS = ['general' => 'General enquiry', 'media' => 'Media', 'supplier' => 'Supplier / partnership', 'feedback' => 'Feedback on an officer or service'];

    protected $fillable = ['name', 'email', 'phone', 'company', 'topic', 'body', 'handled_at'];

    #[\Override]
    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    /** @param Builder<self> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('handled_at');
    }

    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic] ?? $this->topic;
    }
}
