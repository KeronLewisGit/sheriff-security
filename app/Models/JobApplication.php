<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\Experience;
use App\Enums\Region;
use App\Models\Concerns\HasReference;
use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $reference
 * @property int|null $job_posting_id
 * @property ApplicationStatus $status
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string|null $email
 * @property Region $region
 * @property Experience $experience
 * @property bool $armed_licence
 * @property bool $offshore_ready
 * @property string|null $message
 * @property string|null $staff_notes
 * @property Carbon|null $status_changed_at
 * @property Carbon $created_at
 * @property-read string $full_name
 * @property-read JobPosting|null $jobPosting
 * @property-read Collection<int, ApplicationDocument> $documents
 */
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory, HasReference;

    public const string REFERENCE_PREFIX = 'APP';

    public const array FILE_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    protected $fillable = [
        'job_posting_id', 'status', 'first_name', 'last_name', 'phone', 'email', 'region', 'experience',
        'armed_licence', 'offshore_ready', 'message', 'staff_notes', 'status_changed_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'region' => Region::class,
            'experience' => Experience::class,
            'armed_licence' => 'boolean',
            'offshore_ready' => 'boolean',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        // Applicants type numbers every way; keep a digits-only copy so staff can search them.
        static::saving(function (self $application): void {
            $application->phone_digits = preg_replace('/\D+/', '', (string) $application->phone) ?: null;
        });
    }

    /** @return BelongsTo<JobPosting, $this> */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /** @return HasMany<ApplicationDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function hasDocument(DocumentType $type): bool
    {
        return $this->documents->contains(fn (ApplicationDocument $d) => $d->type === $type);
    }

    public function moveTo(ApplicationStatus $status): void
    {
        if ($this->status !== $status) {
            $this->status = $status;
            $this->status_changed_at = now();
        }
    }

    /**
     * Filters shared by the admin list, the kanban and the CSV export.
     *
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['region'] ?? null, fn (Builder $q, $v) => $q->where('region', $v))
            ->when($filters['job'] ?? null, fn (Builder $q, $v) => $v === 'general' ? $q->whereNull('job_posting_id') : $q->where('job_posting_id', $v))
            ->when($filters['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $digits = preg_replace('/\D+/', '', (string) $term);
                $q->where(function (Builder $w) use ($like, $digits) {
                    $w->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)
                        ->orWhere('reference', 'like', $like)->orWhere('email', 'like', $like);
                    if (strlen((string) $digits) >= 3) {
                        $w->orWhere('phone_digits', 'like', '%'.$digits.'%');
                    }
                });
            });
    }
}
