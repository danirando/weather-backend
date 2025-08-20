<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Ricerca città tramite Geocoding API
     */
    public function searchCity($city)
    {
        try {
            $response = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $city,
                'limit' => 5,
                'appid' => config('services.openweather.key'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Errore ricerca città'], 500);
        }
    }

    /**
     * Dati meteo attuali per cityId (lat,lon)
     */
    public function getCurrentWeather(Request $request, $cityId)
    {
        try {
            // cityId sarà tipo "lat,lon" es: "45.4642,9.19" per Milano
            [$lat, $lon] = explode(',', $cityId);

            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Errore meteo attuale'], 500);
        }
    }

    /**
     * Previsioni a 5 giorni
     */
    public function getForecast(Request $request, $cityId)
    {
        try {
            [$lat, $lon] = explode(',', $cityId);

            $response = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'lang' => 'it',
                'appid' => config('services.openweather.key'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Errore previsioni'], 500);
        }
    }
}
