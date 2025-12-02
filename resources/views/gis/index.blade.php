@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
@endsection

@section('content')

<h1 class="text-xl font-bold mb-4">Peta Kesesuaian Lahan</h1>

<div id="map" class="w-full" style="height: 600px;"></div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// Inisialisasi Map
var map = L.map('map').setView([-6.2, 106.15], 10);

// Basemap OSM
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
}).addTo(map);

// Warna kelas sesuai aturan klasifikasi
function warnaKelas(kelas) {
    return {
        'S1': '#00aa00',    // hijau tua
        'S2': '#d4d40d',    // kuning
        'S3': '#ff8800',    // oranye
        'N' : '#cc0000'     // merah
    }[kelas] || '#888';     // default abu
}

// ---- FETCH SEMUA GEOJSON SEKALI SAJA ---- //
fetch("{{ route('map.geojson') }}")
    .then(r => r.json())
    .then(json => {

        L.geoJSON(json, {
            style: feature => ({
                color: warnaKelas(feature.properties.kelas_kesesuaian),
                weight: 2,
                fillOpacity: 0.5
            }),
            onEachFeature: (feature, layer) => {
                let p = feature.properties;

                layer.bindPopup(`
                    <div class='text-sm'>
                        <b>Lokasi:</b> ${p.lokasi}<br>
                        <b>Kelas Kesesuaian:</b> ${p.kelas_kesesuaian ?? '-'}<br>
                        <b>Skor Total:</b> ${p.nilai_total ?? '-'}<br>
                        <b>Ranking VIKOR:</b> ${p.vikor_ranking ?? '-'}<br>
                        <b>Q-Value:</b> ${p.vikor_q ?? '-'}<br>
                    </div>
                `);
            }
        }).addTo(map);

    })
    .catch(err => console.error("Gagal load GeoJSON:", err));
</script>
@endsection
