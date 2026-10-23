<?php

namespace App\Models;

use App\Enums\Industry;
use App\Enums\ProposalStatus;
use App\Enums\SiteType;
use App\Models\Concerns\HasReference;
use Database\Factories\ProposalRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * A request for proposal (RFP) from a prospective client.
 *
 * @property int $id
 * @property string $reference
 * @property ProposalStatus $status
 * @property string $company
 * @property string $contact_name
 * @property string|null $job_title
 * @property string $email
 * @property string $phone
 * @property Industry $industry
 * @property SiteType $site_type
 * @property string $site_location
 * @property int $posts
 * @property string $coverage
 * @property string $armed
 * @property Carbon|null $start_date
 * @property string|null $contract_term
 * @property string|null $details
 * @property int|null $owner_id
 * @property string|null $staff_notes
 * @property string|null $value_estimate
 * @property Carbon|null $responded_at
 * @property Carbon|null $status_changed_at
 * @property Carbon $created_at
 * @property-read User|null $owner
 * @property-read Collection<int, ProposalDocument> $documents
 */
class ProposalRequest extends Model
{
    /** @use HasFactory<ProposalRequestFactory> */
    use HasFactory, HasReference;

    public const string REFERENCE_PREFIX = 'RFP';

    public const array COVERAGE = ['day' => 'Day shift', 'night' => 'Night shift', '24_7' => '24 hours, 7 days', 'event' => 'One-off / short term'];

    public const array ARMED = ['armed' => 'Armed', 'unarmed' => 'Unarmed', 'mixed' => 'A mix', 'advise' => 'Advise us'];

    public const array TERMS = ['one_off' => 'One-off', 'under_1y' => 'Under a year', '1y' => 'One year', 'multi' => 'Multi-year', 'unsure' => 'Not decided'];

    public const array FILE_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];

    protected $fillable = [
        'status', 'company', 'contact_name', 'job_title', 'email', 'phone', 'industry', 'site_type', 'site_location',
        'posts', 'coverage', 'armed', 'start_date', 'contract_term', 'details', 'owner_id', 'staff_notes',
        'value_estimate', 'responded_at', 'status_changed_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'industry' => Industry::class,
            'site_type' => SiteType::class,
            'posts' => 'integer',
            'start_date' => 'date',
            'value_estimate' => 'decimal:2',
            'responded_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<ProposalDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ProposalDocument::class);
    }

    /** Leaving "New" for the first time counts as the first response. */
    public function moveTo(ProposalStatus $status): void
    {
        if ($this->status === $status) {
            return;
        }
        $this->status = $status;
        $this->status_changed_at = now();
        if ($status !== ProposalStatus::New) {
            $this->responded_at ??= now();
        }
    }

    public static function responseHours(): int
    {
        return max(1, (int) setting('rfp_response_hours', '24'));
    }

    /**
     * Still "New" and older than the response target: the number the dashboard leads with.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', ProposalStatus::New)->whereNull('responded_at')
            ->where('created_at', '<=', now()->subHours(self::responseHours()));
    }

    public function isOverdue(): bool
    {
        return $this->status === ProposalStatus::New && $this->responded_at === null
            && $this->created_at->lte(now()->subHours(self::responseHours()));
    }

    public function coverageLabel(): string
    {
        return self::COVERAGE[$this->coverage] ?? $this->coverage;
    }

    public function armedLabel(): string
    {
        return self::ARMED[$this->armed] ?? $this->armed;
    }
}
