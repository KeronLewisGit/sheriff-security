<?php

use App\Models\Setting;

if (! function_exists('sheriff')) {
    /** Static company facts from config/sheriff.php. */
    function sheriff(string $key, mixed $default = null): mixed
    {
        return config('sheriff.'.$key, $default);
    }
}

if (! function_exists('setting')) {
    /** Admin-editable value from the settings table, falling back to config/sheriff.php defaults. */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key) ?? $default ?? config('sheriff.defaults.'.$key);
    }
}

if (! function_exists('gy_digits')) {
    /** E.164 digits for a Guyana number (592 + 7 digits) written any common way, or null. */
    function gy_digits(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?? '';
        if (strlen($digits) === 7) {
            $digits = '592'.$digits;
        }

        return strlen($digits) === 10 && str_starts_with($digits, '592') ? $digits : null;
    }
}

if (! function_exists('tel_href')) {
    function tel_href(?string $number): string
    {
        $digits = gy_digits($number);

        return $digits ? 'tel:+'.$digits : 'tel:'.preg_replace('/[^\d+]/', '', (string) $number);
    }
}

if (! function_exists('format_phone')) {
    /** +592 227 5691 for Guyana numbers, the input unchanged otherwise. */
    function format_phone(?string $number): string
    {
        $digits = gy_digits($number);

        return $digits ? sprintf('+592 %s %s', substr($digits, 3, 3), substr($digits, 6)) : (string) $number;
    }
}

if (! function_exists('input_string')) {
    /** A request value as a string. Arrays (?type[]=x) and other junk become ''. */
    function input_string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}

if (! function_exists('lines_to_array')) {
    /**
     * Textarea with one item per line, bullets optional, to a clean list.
     *
     * @return list<string>
     */
    function lines_to_array(?string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text) ?: [];
        $clean = array_map(fn (string $l) => trim((string) preg_replace('/^(?:[-*]|•)\s*/u', '', $l)), $lines);

        return array_values(array_filter($clean, fn (string $l) => $l !== ''));
    }
}

if (! function_exists('source_link')) {
    /** @return array{label: string, url: string} */
    function source_link(string $key): array
    {
        return config('sheriff.sources.'.$key) ?? ['label' => $key, 'url' => '#'];
    }
}

if (! function_exists('human_bytes')) {
    function human_bytes(int $bytes): string
    {
        return $bytes >= 1_048_576 ? round($bytes / 1_048_576, 1).' MB' : max(1, (int) round($bytes / 1024)).' KB';
    }
}

if (! function_exists('photo')) {
    /**
     * Responsive <img> attributes for a photo in public/img (WebP at 480/960/1920 wide).
     *
     * @return array{src: string, srcset: string, width: int, height: int}
     */
    function photo(string $key): array
    {
        $meta = config('sheriff.photos.'.$key) ?? ['w' => 1920, 'h' => 1280];
        $url = fn (int $w) => asset("img/{$key}-{$w}.webp");

        return [
            'src' => $url(960),
            'srcset' => $url(480).' 480w, '.$url(960).' 960w, '.$url(1920).' 1920w',
            'width' => (int) $meta['w'],
            'height' => (int) $meta['h'],
        ];
    }
}
