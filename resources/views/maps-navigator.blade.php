<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Google Maps Navigation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #map { height: 600px; width: 100%; }
        .autocomplete-dropdown { max-height: 300px; overflow-y: auto; }
        .route-card { transition: all 0.3s; }
        .route-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
                <h1 class="text-3xl font-bold text-white">🗺️ Google Maps Navigation</h1>
                <p class="text-blue-100 mt-2">Find shortest path with real-time traffic</p>
            </div>

            <div class="grid md:grid-cols-3 gap-4 p-6">
                <div class="md:col-span-1 space-y-4">
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            🔵 Starting Location
                        </label>
                        <input type="text" id="origin-input" placeholder="Where from..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autocomplete="off">
                        <div id="origin-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-200 rounded-lg shadow-xl autocomplete-dropdown"></div>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            🔴 Destination
                        </label>
                        <input type="text" id="destination-input" placeholder="Where to..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            autocomplete="off">
                        <div id="destination-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-200 rounded-lg shadow-xl autocomplete-dropdown"></div>
                    </div>

                    <div class="flex items-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <input type="checkbox" id="traffic-toggle" checked class="w-5 h-5 text-blue-600">
                        <label for="traffic-toggle" class="ml-3 text-sm font-medium text-gray-700">
                            🚦 Show real-time traffic
                        </label>
                    </div>

                    <button id="calculate-btn"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        🔍 Find Routes
                    </button>

                    <div id="loading" class="hidden">
                        <div class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
                            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-2 text-blue-600 font-medium">Calculating route...</span>
                        </div>
                    </div>

                    <div id="routes-container" class="space-y-3 mt-4"></div>
                </div>

                <div class="md:col-span-2">
                    <div id="map" class="rounded-lg shadow-lg border-2 border-gray-200"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMap" async defer></script>
    <script>
        let map, directionsRenderer, directionsService;
        let selectedOrigin = null, selectedDestination = null;
        let autocompleteTimer;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 28.6139, lng: 77.2090 },
                zoom: 12,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: false,
                polylineOptions: { strokeColor: '#4285F4', strokeWeight: 6 }
            });
        }

        async function fetchAutocomplete(input, dropdownId) {
            if (input.length < 3) {
                document.getElementById(dropdownId).classList.add('hidden');
                return;
            }

            try {
                const response = await fetch('/api/maps/autocomplete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ input })
                });

                const data = await response.json();
                if (data.success && data.predictions) {
                    showAutocompleteResults(data.predictions, dropdownId);
                }
            } catch (error) {
                console.error('Autocomplete error:', error);
            }
        }

        function showAutocompleteResults(predictions, dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            
            if (predictions.length === 0) {
                dropdown.classList.add('hidden');
                return;
            }

            dropdown.innerHTML = predictions.map(pred => `
                <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0 transition" 
                     onclick="selectPlace('${pred.place_id}', '${pred.description.replace(/'/g, "\\'")}', '${dropdownId}')">
                    <div class="font-semibold text-gray-800">${pred.main_text}</div>
                    <div class="text-sm text-gray-500">${pred.secondary_text}</div>
                </div>
            `).join('');

            dropdown.classList.remove('hidden');
        }

        function selectPlace(placeId, description, dropdownId) {
            const isOrigin = dropdownId === 'origin-dropdown';
            const inputId = isOrigin ? 'origin-input' : 'destination-input';
            
            document.getElementById(inputId).value = description;
            document.getElementById(dropdownId).classList.add('hidden');

            if (isOrigin) {
                selectedOrigin = { place_id: placeId, description };
            } else {
                selectedDestination = { place_id: placeId, description };
            }

            document.getElementById('calculate-btn').disabled = !(selectedOrigin && selectedDestination);
        }

        document.getElementById('origin-input').addEventListener('input', (e) => {
            clearTimeout(autocompleteTimer);
            autocompleteTimer = setTimeout(() => fetchAutocomplete(e.target.value, 'origin-dropdown'), 300);
        });

        document.getElementById('destination-input').addEventListener('input', (e) => {
            clearTimeout(autocompleteTimer);
            autocompleteTimer = setTimeout(() => fetchAutocomplete(e.target.value, 'destination-dropdown'), 300);
        });

        document.getElementById('calculate-btn').addEventListener('click', calculateRoute);

        async function calculateRoute() {
            if (!selectedOrigin || !selectedDestination) return;

            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('routes-container').innerHTML = '';

            try {
                const response = await fetch('/api/maps/calculate-route', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        origin_place_id: selectedOrigin.place_id,
                        destination_place_id: selectedDestination.place_id,
                        consider_traffic: document.getElementById('traffic-toggle').checked
                    })
                });

                const data = await response.json();
                if (data.success) {
                    displayRoutes(data);
                } else {
                    alert('Error: ' + (data.error || 'Route not found'));
                }
            } catch (error) {
                alert('Network error occurred');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        function displayRoutes(data) {
            const container = document.getElementById('routes-container');
            
            container.innerHTML = data.routes.map((route, index) => {
                const trafficInfo = route.traffic_delay ? `
                    <div class="mt-2 p-2 rounded ${
                        route.traffic_delay.status === 'light' ? 'bg-green-50 text-green-700' :
                        route.traffic_delay.status === 'moderate' ? 'bg-yellow-50 text-yellow-700' :
                        'bg-red-50 text-red-700'
                    }">
                        <div class="flex items-center text-sm">
                            <span class="mr-2">🚦</span>
                            <span>Traffic: ${route.traffic_delay.text} delay</span>
                        </div>
                    </div>
                ` : '';

                return `
                    <div class="route-card p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500" 
                         onclick="selectRoute(${index})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-bold text-lg text-gray-800">Route ${route.route_number}</div>
                                <div class="text-sm text-gray-600 mt-1">${route.summary}</div>
                            </div>
                            ${index === 0 ? '<span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">Fastest</span>' : ''}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <div class="flex items
                            ${trafficInfo}
                            </div>
                </div>
            `;
        }).join('');

        selectRoute(0);
        window.routesData = data;
    }

    function selectRoute(index) {
        if (!window.routesData) return;

        const request = {
            origin: { placeId: selectedOrigin.place_id },
            destination: { placeId: selectedDestination.place_id },
            travelMode: 'DRIVING',
            drivingOptions: {
                departureTime: new Date(),
                trafficModel: 'bestguess'
            },
            provideRouteAlternatives: true
        };

        directionsService.route(request, (result, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
                directionsRenderer.setRouteIndex(index);
            }
        });

        document.querySelectorAll('.route-card').forEach((card, i) => {
            if (i === index) {
                card.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                card.classList.remove('border-blue-500', 'bg-blue-50');
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#origin-input') && !e.target.closest('#origin-dropdown')) {
            document.getElementById('origin-dropdown').classList.add('hidden');
        }
        if (!e.target.closest('#destination-input') && !e.target.closest('#destination-dropdown')) {
            document.getElementById('destination-dropdown').classList.add('hidden');
        }
    });
</script>