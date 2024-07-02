<?php

namespace App\Utils;

class ColorConverter
{
    public static function hexToRgba($hex)
    {
        $hex = str_replace('#', '', $hex);

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return sprintf('%d, %d, %d', $r, $g, $b);
    }
}