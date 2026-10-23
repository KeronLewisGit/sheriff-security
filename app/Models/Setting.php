<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Admin-editable key/value settings (contact details, response targets).
 *
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Every editable key, with its admin label and hint. Order is the form order. */
    public const array FIELDS = [
        'phone' => ['Main phone', 'Shown in the header, footer and mobile call button.'],
        'phone_alt' => ['Second phone', 'Optional.'],
        'whatsapp' => ['WhatsApp number', 'Optional. Digits with country code, e.g. 5926001234.'],
        'command_centre_line' => ['24-hour command centre line', 'Optional. Shown on the alarm service page for urgent faults.'],
        'email' => ['Public email', 'Shown on the contact page.'],
        'proposals_email' => ['Proposals email', 'Optional. Shown on the request-a-proposal page.'],
        'address_line' => ['Street address', ''],
        'address_city' => ['City', ''],
        'office_hours' => ['Office hours', ''],
        'facebook' => ['Facebook URL', ''],
        'instagram' => ['Instagram URL', ''],
        'linkedin' => ['LinkedIn URL', ''],
        'rfp_response_hours' => ['Proposal response target (hours)', 'The dashboard counts proposal requests still "New" after this many hours.'],
        'vendor_link_days' => ['Vendor pack link lifetime (days)', 'How long an approved download link works.'],
        'notify_email' => ['Notification emails', 'Who is emailed about new submissions. Comma separated.'],
    ];

    /** @return array<string, string|null> */
    public static function allCached(): array
    {
        try {
            return Cache::remember('settings.all', 3600, fn () => Schema::hasTable('settings') ? static::query()->pluck('value', 'key')->all() : []);
        } catch (Throwable) {
            return []; // No database yet (first install): config defaults apply.
        }
    }

    public static function value(string $key): ?string
    {
        $all = static::allCached();

        // A saved empty value means "hide it", so only a missing row falls back to the default.
        return array_key_exists($key, $all) ? (string) $all[$key] : null;
    }

    /** @param array<string, string|null> $values */
    public static function setMany(array $values): void
    {
        foreach ($values as $k => $v) {
            static::query()->updateOrCreate(['key' => $k], ['value' => $v]);
        }
        Cache::forget('settings.all');
    }
}
