<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $job_application_id
 * @property DocumentType $type
 * @property string $path
 * @property string $original_name
 * @property int $size
 * @property-read JobApplication $application
 */
class ApplicationDocument extends Model
{
    protected $fillable = ['type', 'path', 'original_name', 'size'];

    #[\Override]
    protected function casts(): array
    {
        return ['type' => DocumentType::class, 'size' => 'integer'];
    }

    /** @return BelongsTo<JobApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}
