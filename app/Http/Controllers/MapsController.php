<?php

namespace App\Http\Controllers;
use App\Services\GoogleMapsService;
use Illuminate\Http\Request;

class MapsController extends Controller
{
    //
   
    protected $mapsService;

    public function __construct(GoogleMapsService $mapsService)
    {
        $this->mapsService = $mapsService;
    }

    public function autocomplete(Request $request)
    {
        $request->validate(['input' => 'required|string|min:3']);
        return response()->json(
            $this->mapsService->autocomplete($request->input)
        );
    }

    public function calculateRoute(Request $request)
    {
        $request->validate([
            'origin_place_id' => 'required|string',
            'destination_place_id' => 'required|string',
            'consider_traffic' => 'boolean'
        ]);

        $result = $this->mapsService->calculateRoute(
            $request->origin_place_id,
            $request->destination_place_id,
            $request->consider_traffic ?? true
        );

        return response()->json($result);
    }
}
