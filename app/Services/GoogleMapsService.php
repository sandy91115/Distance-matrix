<?php

namespace App\Services;

use App\Models\ApiSetting;
use App\Models\RouteCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = ApiSetting::get('google_maps_api_key');
    }

    public function autocomplete($input)
    {
        //print_r($input);
        if (!$this->apiKey || strlen($input) < 3) {
            return ['success' => false, 'error' => 'Invalid input'];
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'input' => $input,
                'key' => $this->apiKey,
                'language' => 'en',
                'components' => 'country:in'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK') {
                    return [
                        'success' => true,
                        'predictions' => collect($data['predictions'])->map(function($prediction) {
                            return [
                                'place_id' => $prediction['place_id'],
                                'description' => $prediction['description'],
                                'main_text' => $prediction['structured_formatting']['main_text'] ?? '',
                                'secondary_text' => $prediction['structured_formatting']['secondary_text'] ?? ''
                            ];
                        })
                    ];
                }
            }

            return ['success' => false, 'error' => 'No results found'];
        } catch (\Exception $e) {
            Log::error('Autocomplete Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'API Error'];
        }
    }

    public function getPlaceDetails($placeId)
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'geometry,formatted_address',
                'key' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK') {
                    return [
                        'success' => true,
                        'lat' => $data['result']['geometry']['location']['lat'],
                        'lng' => $data['result']['geometry']['location']['lng'],
                        'address' => $data['result']['formatted_address']
                    ];
                }
            }

            return ['success' => false];
        } catch (\Exception $e) {
            Log::error('Place Details Error: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    public function calculateRoute($originPlaceId, $destinationPlaceId, $considerTraffic = true)
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'API key not configured'];
        }

        if (!$considerTraffic) {
            $cached = RouteCache::getCached($originPlaceId, $destinationPlaceId);
            if ($cached) {
                return array_merge(['success' => true, 'cached' => true], $cached->route_data);
            }
        }

        try {
            $origin = $this->getPlaceDetails($originPlaceId);
            $destination = $this->getPlaceDetails($destinationPlaceId);

            if (!$origin['success'] || !$destination['success']) {
                return ['success' => false, 'error' => 'Invalid locations'];
            }

            $params = [
                'origin' => "place_id:{$originPlaceId}",
                'destination' => "place_id:{$destinationPlaceId}",
                'key' => $this->apiKey,
                'language' => 'en',
                'units' => 'metric',
                'alternatives' => true,
            ];

            if ($considerTraffic) {
                $params['departure_time'] = 'now';
                $params['traffic_model'] = 'best_guess';
            }

            $response = Http::timeout(15)->get(
                'https://maps.googleapis.com/maps/api/directions/json',
                $params
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    $routes = $this->parseRoutes($data['routes'], $considerTraffic);
                    
                    $result = [
                        'success' => true,
                        'routes' => $routes,
                        'origin' => [
                            'lat' => $origin['lat'],
                            'lng' => $origin['lng'],
                            'address' => $origin['address']
                        ],
                        'destination' => [
                            'lat' => $destination['lat'],
                            'lng' => $destination['lng'],
                            'address' => $destination['address']
                        ],
                        'has_traffic_data' => $considerTraffic,
                        'cached' => false
                    ];

                    if (!$considerTraffic) {
                        RouteCache::store($originPlaceId, $destinationPlaceId, $result, 1440);
                    }

                    return $result;
                }

                return ['success' => false, 'error' => $data['error_message'] ?? 'Route not found'];
            }

            return ['success' => false, 'error' => 'API request failed'];

        } catch (\Exception $e) {
            Log::error('Route Calculation Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred'];
        }
    }

    private function parseRoutes($routes, $hasTraffic)
    {
        return collect($routes)->map(function($route, $index) use ($hasTraffic) {
            $leg = $route['legs'][0];
            
            $result = [
                'route_number' => $index + 1,
                'summary' => $route['summary'],
                'distance' => [
                    'text' => $leg['distance']['text'],
                    'value' => $leg['distance']['value']
                ],
                'duration' => [
                    'text' => $leg['duration']['text'],
                    'value' => $leg['duration']['value']
                ],
                'polyline' => $route['overview_polyline']['points'],
                'steps' => collect($leg['steps'])->map(function($step) {
                    return [
                        'distance' => $step['distance']['text'],
                        'duration' => $step['duration']['text'],
                        'instruction' => strip_tags($step['html_instructions']),
                        'maneuver' => $step['maneuver'] ?? 'straight'
                    ];
                })
            ];

            if ($hasTraffic && isset($leg['duration_in_traffic'])) {
                $result['duration_in_traffic'] = [
                    'text' => $leg['duration_in_traffic']['text'],
                    'value' => $leg['duration_in_traffic']['value']
                ];
                
                $trafficDelay = $leg['duration_in_traffic']['value'] - $leg['duration']['value'];
                $result['traffic_delay'] = [
                    'text' => $this->formatDuration($trafficDelay),
                    'value' => $trafficDelay,
                    'status' => $this->getTrafficStatus($trafficDelay)
                ];
            }

            return $result;
        })->values()->all();
    }

    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . ' hr ' . $remainingMinutes . ' min';
    }

    private function getTrafficStatus($delaySeconds)
    {
        if ($delaySeconds < 300) return 'light';
        if ($delaySeconds < 900) return 'moderate';
        return 'heavy';
    }
}