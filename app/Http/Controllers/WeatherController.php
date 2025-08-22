<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        $ttlMemory = now()->addMinutes(10);  // in-memory breve
        $ttlPersistent = now()->addDay();    // persistente lungo

        // Cache in-memory
        $data = Cache::store('array')->get($cacheKey);

        // Cache persistente
        if (!$data) {
            $data = Cache::store('database')->get($cacheKey);
        }

        // Cache miss → API
        if (!$data) {
            $response = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $city,
                'limit' => 5,
                'appid' => config('services.openweather.key'),
            ]);
            $data = $response->json();

            Log::info("Cache miss for key: {$cacheKey}");

            // Salvo in entrambe le cache
            Cache::store('array')->put($cacheKey, $data, $ttlMemory);
            Cache::store('database')->put($cacheKey, $data, $ttlPersistent);
        }

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=86400')  // 24h
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

        $ttlMemory = now()->addMinutes(5);
        $ttlPersistent = now()->addHour();

        // Cache in-memory
        $data = Cache::store('array')->get($cacheKey);

        // Cache persistente
        if (!$data) {
            $data = Cache::store('database')->get($cacheKey);
        }

        // Cache miss → API
        if (!$data) {
            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);
            $data = $response->json();

            Log::info("Cache miss for key: {$cacheKey}");

            // Salvo in entrambe le cache
            Cache::store('array')->put($cacheKey, $data, $ttlMemory);
            Cache::store('database')->put($cacheKey, $data, $ttlPersistent);
        }

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=300')       // 5 minuti
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

        $ttlMemory = now()->addMinutes(15);
        $ttlPersistent = now()->addHour();

        // Cache in-memory
        $data = Cache::store('array')->get($cacheKey);

        // Cache persistente
        if (!$data) {
            $data = Cache::store('database')->get($cacheKey);
        }

        // Cache miss → API
        if (!$data) {
            $response = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);
            $data = $response->json();

            Log::info("Cache miss for key: {$cacheKey}");

            // Salvo in entrambe le cache
            Cache::store('array')->put($cacheKey, $data, $ttlMemory);
            Cache::store('database')->put($cacheKey, $data, $ttlPersistent);
        }

        return response()->json($data)
            ->header('Cache-Control', 'public, max-age=3600')   // 1 ora
            ->header('Expires', now()->addHour()->toRfc7231String());
    }


}
