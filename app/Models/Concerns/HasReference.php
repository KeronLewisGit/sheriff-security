<?php

namespace App\Models\Concerns;

/**
 * Human-friendly reference numbers (RFP-26-00012) written once the row has an id.
 * Each model sets REFERENCE_PREFIX.
 */
trait HasReference
{
    public static function bootHasReference(): void
    {
        static::creating(function (self $model): void {
            $model->reference ??= 'pending-'.bin2hex(random_bytes(5));
        });
        static::created(function (self $model): void {
            $model->forceFill([
                'reference' => sprintf('%s-%s-%05d', static::REFERENCE_PREFIX, $model->created_at->format('y'), $model->id),
            ])->saveQuietly();
        });
    }

    /** Case- and space-insensitive form of what a visitor typed, for lookups. */
    public static function normaliseReference(string $value): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', $value));
    }
}
