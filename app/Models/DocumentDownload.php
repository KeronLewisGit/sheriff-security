<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_request_id
 * @property int|null $compliance_document_id
 * @property string $document_title
 * @property string|null $ip
 * @property string|null $user_agent
 * @property Carbon $created_at
 */
class DocumentDownload extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['vendor_request_id', 'compliance_document_id', 'document_title', 'ip', 'user_agent'];

    /** @return BelongsTo<VendorRequest, $this> */
    public function vendorRequest(): BelongsTo
    {
        return $this->belongsTo(VendorRequest::class);
    }
}
