<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherController extends Controller
{
    public function get(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
       
        if (!$lat || !$lon) {
            return response()->json(['error' => 'Missing lat/lon'], 422);
        }

        // Cache key based on coordinates
        $cacheKey = "weather_{$lat}_{$lon}";
         Cache::forget($cacheKey);
        // Try to get cached weather
        $weather = Cache::get($cacheKey);

        if (!$weather) {
            $apiKey = env('OPENWEATHER_API_KEY');

            if (!$apiKey) {
                return response()->json(['error' => 'OpenWeather API key not set'], 500);
            }

            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'appid' => $apiKey,
                'lang' => 'en'
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch weather'], 500);
            }

            $data = $response->json();

            $weather = [
                'city' => $data['name'] ?? '',
                'country' => $data['sys']['country'] ?? '',
                'temperature' => $data['main']['temp'] ?? null,
                'description' => $data['weather'][0]['description'] ?? '',
            ];

            // Cache for 1 hour
            Cache::put($cacheKey, $weather, 3600);
        }

        return response()->json($weather);
    }
}
