<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $proposal_request_id
 * @property string $path
 * @property string $original_name
 * @property int $size
 */
class ProposalDocument extends Model
{
    protected $fillable = ['path', 'original_name', 'size'];

    /** @return BelongsTo<ProposalRequest, $this> */
    public function proposalRequest(): BelongsTo
    {
        return $this->belongsTo(ProposalRequest::class);
    }
}
