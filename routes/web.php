
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapsController;

Route::middleware(['throttle:120,1'])->group(function() {
    Route::post('/api/maps/autocomplete', [MapsController::class, 'autocomplete']);
    Route::post('/api/maps/calculate-route', [MapsController::class, 'calculateRoute']);
});

Route::get('/maps', function () {
    return view('maps-navigator');
});

Route::get('/', function () {
    return view('welcome');
});
