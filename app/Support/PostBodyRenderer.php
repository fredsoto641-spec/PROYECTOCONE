<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class PostBodyRenderer
{
    public static function render(?string $body): HtmlString
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $body) ?: [];
        $html = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^!\[([^\]]*)\]\((https?:\/\/[^)\s]+)\)$/i', $trimmed, $matches)) {
                $url = $matches[2];

                if (self::isSafeUrl($url)) {
                    $html[] = sprintf(
                        '<figure class="my-6 overflow-hidden rounded-2xl border border-[#E5E7EB] bg-[#F8F8F8]"><img src="%s" alt="%s" class="w-full object-cover" loading="lazy"></figure>',
                        e($url),
                        e($matches[1] ?: 'Imagen del post'),
                    );
                }

                continue;
            }

            $html[] = '<p>'.self::renderInline($trimmed).'</p>';
        }

        return new HtmlString(implode("\n", $html));
    }

    private static function renderInline(string $text): string
    {
        $parts = preg_split('/(\[[^\]]+\]\(https?:\/\/[^)\s]+\))/', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        return collect($parts)
            ->map(function (string $part): string {
                if (preg_match('/^\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)$/i', $part, $matches)) {
                    return self::link($matches[2], $matches[1]);
                }

                return self::linkifyText($part);
            })
            ->implode('');
    }

    private static function linkifyText(string $text): string
    {
        $parts = preg_split('/(https?:\/\/[^\s]+)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        return collect($parts)
            ->map(fn (string $part): string => preg_match('/^https?:\/\//i', $part)
                ? self::link($part, $part)
                : e($part))
            ->implode('');
    }

    private static function link(string $url, string $label): string
    {
        if (! self::isSafeUrl($url)) {
            return e($label);
        }

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="font-semibold text-[#E91E63] underline decoration-[#E91E63]/30 underline-offset-4 transition hover:text-[#C2185B]">%s</a>',
            e($url),
            e($label),
        );
    }

    private static function isSafeUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
