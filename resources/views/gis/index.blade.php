@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
@endsection

@section('content')

<h1 class="text-xl font-bold mb-4">Peta Kesesuaian Lahan</h1>

<div id="map" style="height: 600px;"></div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
var map = L.map('map').setView([-6.2, 106.15], 9);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
}).addTo(map);


function warnaKelas(kelas) {
    return {
        'S1': 'green',
        'S2': 'yellow',
        'S3': 'orange',
        'N' : 'red'
    }[kelas];
}

@foreach($data as $alt)
fetch("{{ asset('storage/'.$alt->geojson_path) }}")
    .then(res => res.json())
    .then(geo => {
        var layer = L.geoJSON(geo, {
            style: {
                color: warnaKelas("{{ $alt->kelas_kesesuaian }}"),
                weight: 2,
                fillOpacity: 0.5
            }
        }).addTo(map);

        layer.bindPopup(`
            <b>Lokasi:</b> {{ $alt->lokasi }}<br>
            <b>Kelas:</b> {{ $alt->kelas_kesesuaian }}
        `);

        // Zoom ke centroid
        @if($alt->lat && $alt->lng)
            map.setView([{{ $alt->lat }}, {{ $alt->lng }}], 12);
        @endif
    });
@endforeach

</script>
@endsection
