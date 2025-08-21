<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    /**
     * Ricerca città tramite Geocoding API
     * Cache server lunga: 24h
     * Cache browser: 24h
     */
    public function searchCity($city)
    {
        $cacheKey = "search_city_" . strtolower($city);

        $data = Cache::remember($cacheKey, now()->addDay(), function () use ($city) {
            $response = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $city,
                'limit' => 5,
                'appid' => config('services.openweather.key'),
            ]);
            return $response->json();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=86400') // 24h
            ->header('Expires', now()->addDay()->toRfc7231String());
    }

    /**
     * Dati meteo attuali
     * Cache server breve: 5 minuti
     * Cache browser: 5 minuti
     */
    public function getCurrentWeather(Request $request, $cityId)
    {
        [$lat, $lon] = explode(',', $cityId);
        $cacheKey = "weather_current_{$lat}_{$lon}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($lat, $lon) {
            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);
            return $response->json();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=300') // 5 minuti
            ->header('Expires', now()->addMinutes(5)->toRfc7231String());
    }

    /**
     * Previsioni a 5 giorni
     * Cache server media: 1 ora
     * Cache browser: 1 ora
     */
    public function getForecast(Request $request, $cityId)
    {
        [$lat, $lon] = explode(',', $cityId);
        $cacheKey = "weather_forecast_{$lat}_{$lon}";

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($lat, $lon) {
            $response = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);
            return $response->json();
        });

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600') // 1 ora
            ->header('Expires', now()->addHour()->toRfc7231String());
    }
}
