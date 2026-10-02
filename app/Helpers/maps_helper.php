<?php

if (!function_exists('google_maps_embed_url')) {
    function google_maps_embed_url($latitude, $longitude): ?string
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return sprintf('https://www.google.com/maps?q=%s,%s&z=16&output=embed', $lat, $lng);
    }
}
