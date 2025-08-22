<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/search/{city}', [WeatherController::class, 'searchCity']);
Route::get('/weather/{cityId}', [WeatherController::class, 'getCurrentWeather']);
Route::get('/forecast/{cityId}', [WeatherController::class, 'getForecast']);
