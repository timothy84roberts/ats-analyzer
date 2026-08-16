<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class RichTextSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return self::purifier()->purify($html);
    }

    /**
     * Convert sanitized rich text to readable plain text with paragraph breaks.
     * strip_tags alone collapses </p><p> into one line (e.g. "jobWe").
     */
    public static function toPlainText(?string $html): string
    {
        $clean = self::sanitize($html);
        if ($clean === '') {
            return '';
        }

        $text = $clean;
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\s*\/\s*(p|div|h[1-6]|li|blockquote|pre|tr)\s*>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\s*li\b[^>]*>/i', '- ', $text) ?? $text;
        $text = preg_replace('/<\s*\/\s*(ul|ol)\s*>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text); // &nbsp;
        $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n[ \t]+/", "\n", $text) ?? $text;
        $text = preg_replace('/[ \t]{2,}/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        $cachePath = storage_path('app/htmlpurifier');
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cachePath);

        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
            'ul', 'ol', 'li',
            'a[href|target|rel|title]',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'blockquote', 'code', 'pre',
        ]));
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank' => true]);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
