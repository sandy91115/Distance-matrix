<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteCache extends Model
{
    //
        protected $table = 'route_cache';
    protected $fillable = ['origin_hash', 'destination_hash', 'route_data', 'expires_at'];
    protected $casts = ['route_data' => 'array', 'expires_at' => 'datetime'];

    public static function getCached($originPlaceId, $destinationPlaceId)
    {
        $originHash = md5($originPlaceId);
        $destinationHash = md5($destinationPlaceId);

        return self::where('origin_hash', $originHash)
            ->where('destination_hash', $destinationHash)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    public static function store($originPlaceId, $destinationPlaceId, $routeData, $ttlMinutes = 30)
    {
        return self::updateOrCreate(
            [
                'origin_hash' => md5($originPlaceId),
                'destination_hash' => md5($destinationPlaceId)
            ],
            [
                'route_data' => $routeData,
                'expires_at' => Carbon::now()->addMinutes($ttlMinutes)
            ]
        );
    }
}
