<?php

namespace App\Models;

use App\Enums\ComplianceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A document procurement teams can request in the vendor pack.
 *
 * @property int $id
 * @property string $title
 * @property ComplianceCategory $category
 * @property string|null $description
 * @property string|null $path
 * @property string|null $original_name
 * @property int|null $size
 * @property Carbon|null $issued_on
 * @property Carbon|null $expires_on
 * @property bool $is_listed
 * @property int $sort
 */
class ComplianceDocument extends Model
{
    public const array FILE_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];

    protected $fillable = ['title', 'category', 'description', 'path', 'original_name', 'size', 'issued_on', 'expires_on', 'is_listed', 'sort'];

    #[\Override]
    protected function casts(): array
    {
        return [
            'category' => ComplianceCategory::class,
            'issued_on' => 'date',
            'expires_on' => 'date',
            'is_listed' => 'boolean',
            'size' => 'integer',
            'sort' => 'integer',
        ];
    }

    /** @return HasMany<DocumentDownload, $this> */
    public function downloads(): HasMany
    {
        return $this->hasMany(DocumentDownload::class);
    }

    /** @param Builder<self> $query */
    public function scopeListed(Builder $query): void
    {
        $query->where('is_listed', true)->orderBy('sort')->orderBy('title');
    }

    public function hasFile(): bool
    {
        return $this->path !== null && $this->path !== '';
    }

    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    public function expiresSoon(): bool
    {
        return $this->expires_on !== null && ! $this->isExpired() && $this->expires_on->lte(now()->addDays(60));
    }
}
