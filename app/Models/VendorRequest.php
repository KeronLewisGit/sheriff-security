<?php

namespace App\Models;

use App\Enums\VendorStatus;
use App\Models\Concerns\HasReference;
use Database\Factories\VendorRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * A procurement team's request for the vendor prequalification pack.
 * Approval issues a random access token and an expiry; the emailed link is a
 * temporary signed URL over that token, so it can be revoked or left to expire.
 *
 * @property int $id
 * @property string $reference
 * @property VendorStatus $status
 * @property string $company
 * @property string $contact_name
 * @property string|null $job_title
 * @property string $email
 * @property string|null $phone
 * @property string $purpose
 * @property string|null $tender_reference
 * @property string|null $message
 * @property string|null $access_token
 * @property Carbon|null $expires_at
 * @property int|null $decided_by
 * @property Carbon|null $decided_at
 * @property string|null $decision_note
 * @property Carbon $created_at
 * @property-read Collection<int, ComplianceDocument> $documents
 * @property-read Collection<int, DocumentDownload> $downloads
 * @property-read User|null $decider
 */
class VendorRequest extends Model
{
    /** @use HasFactory<VendorRequestFactory> */
    use HasFactory, HasReference;

    public const string REFERENCE_PREFIX = 'VP';

    public const array PURPOSES = [
        'prequalification' => 'Supplier prequalification / vendor registration',
        'tender' => 'A live tender or RFQ',
        'audit' => 'Contractor audit or renewal',
        'other' => 'Something else',
    ];

    protected $fillable = ['status', 'company', 'contact_name', 'job_title', 'email', 'phone', 'purpose', 'tender_reference', 'message', 'decision_note'];

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
            'expires_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<ComplianceDocument, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(ComplianceDocument::class)->orderBy('sort');
    }

    /** @return HasMany<DocumentDownload, $this> */
    public function downloads(): HasMany
    {
        return $this->hasMany(DocumentDownload::class)->latest('created_at');
    }

    /** @return BelongsTo<User, $this> */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function approve(User $by, int $days): void
    {
        $this->status = VendorStatus::Approved;
        $this->access_token = Str::random(48);
        $this->expires_at = now()->addDays($days);
        $this->decided_by = $by->id;
        $this->decided_at = now();
        $this->save();
    }

    public function decline(User $by, ?string $note): void
    {
        $this->status = VendorStatus::Declined;
        $this->decision_note = $note;
        $this->decided_by = $by->id;
        $this->decided_at = now();
        $this->access_token = null;
        $this->save();
    }

    public function revoke(): void
    {
        $this->status = VendorStatus::Revoked;
        $this->access_token = null;
        $this->save();
    }

    public function linkIsLive(): bool
    {
        return $this->status === VendorStatus::Approved && $this->access_token !== null
            && $this->expires_at !== null && $this->expires_at->isFuture();
    }

    /** The signed link to the download page, valid until expires_at. */
    public function packUrl(): ?string
    {
        if (! $this->linkIsLive()) {
            return null;
        }

        return URL::temporarySignedRoute('vendor-pack.show', $this->expires_at, ['token' => $this->access_token]);
    }

    public function downloadUrl(ComplianceDocument $document): ?string
    {
        if (! $this->linkIsLive()) {
            return null;
        }

        return URL::temporarySignedRoute('vendor-pack.download', $this->expires_at, ['token' => $this->access_token, 'document' => $document->id]);
    }

    public function purposeLabel(): string
    {
        return self::PURPOSES[$this->purpose] ?? $this->purpose;
    }
}
