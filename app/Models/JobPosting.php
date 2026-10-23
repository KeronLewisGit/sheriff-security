<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $department
 * @property string $location
 * @property EmploymentType $employment_type
 * @property JobStatus $status
 * @property int $openings
 * @property string $summary
 * @property string|null $duties
 * @property string|null $requirements
 * @property Carbon|null $closes_on
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    public const array DEPARTMENTS = ['Operations', 'Energy & offshore', 'Marine', 'Cash in transit', 'Alarms', 'Office'];

    protected $fillable = ['slug', 'title', 'department', 'location', 'employment_type', 'status', 'openings', 'summary', 'duties', 'requirements', 'closes_on'];

    #[\Override]
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'status' => JobStatus::class,
            'closes_on' => 'date',
            'openings' => 'integer',
        ];
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<JobApplication, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** @param Builder<self> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', JobStatus::Open)
            ->where(fn (Builder $q) => $q->whereNull('closes_on')->orWhereDate('closes_on', '>=', today()));
    }

    /** @return list<string> */
    public function lines(string $field): array
    {
        return lines_to_array($this->getAttribute($field));
    }
}
