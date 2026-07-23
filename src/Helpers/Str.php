<?php

declare(strict_types=1);

namespace App\Helpers;

final class Str
{
    /** Slug URL-safe (accents et ponctuation gérés). */
    public static function slug(string $text, string $fallback = 'ressource'): string
    {
        $map = ['à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                'î'=>'i','ï'=>'i','í'=>'i','ì'=>'i','ô'=>'o','ö'=>'o','ó'=>'o','ò'=>'o','õ'=>'o','ù'=>'u','û'=>'u',
                'ü'=>'u','ú'=>'u','ÿ'=>'y','ñ'=>'n','œ'=>'oe','æ'=>'ae'];
        $text = strtr(mb_strtolower(trim($text)), $map);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : $fallback;
    }
}
