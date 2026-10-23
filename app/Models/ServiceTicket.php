<?php

namespace App\Models;

use App\Enums\TicketIssue;
use App\Enums\TicketStatus;
use App\Models\Concerns\HasReference;
use Database\Factories\ServiceTicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Alarm service request or fault report from a Sheriff Alarms customer.
 *
 * @property int $id
 * @property string $reference
 * @property TicketStatus $status
 * @property TicketIssue $issue
 * @property bool $urgent
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string|null $account_number
 * @property string $site_address
 * @property string $description
 * @property string|null $preferred_time
 * @property Carbon|null $visit_on
 * @property string|null $public_note
 * @property string|null $staff_notes
 * @property Carbon|null $status_changed_at
 * @property Carbon|null $resolved_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ServiceTicket extends Model
{
    /** @use HasFactory<ServiceTicketFactory> */
    use HasFactory, HasReference;

    public const string REFERENCE_PREFIX = 'SA';

    public const array TIMES = ['morning' => 'Morning (8 to 12)', 'afternoon' => 'Afternoon (12 to 5)', 'any' => 'Any time'];

    protected $fillable = [
        'status', 'issue', 'urgent', 'name', 'email', 'phone', 'account_number', 'site_address', 'description',
        'preferred_time', 'visit_on', 'public_note', 'staff_notes', 'status_changed_at', 'resolved_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'issue' => TicketIssue::class,
            'urgent' => 'boolean',
            'visit_on' => 'date',
            'status_changed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function moveTo(TicketStatus $status): void
    {
        if ($this->status === $status) {
            return;
        }
        $this->status = $status;
        $this->status_changed_at = now();
        $this->resolved_at = $status === TicketStatus::Resolved ? now() : ($status->isOpen() ? null : $this->resolved_at);
    }

    /** @param Builder<self> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::Received, TicketStatus::Scheduled, TicketStatus::InProgress]);
    }

    /** Public lookup: both the reference and the email on the ticket must match. */
    public static function lookup(string $reference, string $email): ?self
    {
        return self::query()
            ->where('reference', self::normaliseReference($reference))
            ->whereRaw('lower(email) = ?', [strtolower(trim($email))])
            ->first();
    }
}
