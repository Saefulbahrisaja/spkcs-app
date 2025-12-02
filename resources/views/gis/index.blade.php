@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
    #loading {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 8px;
        display: none;
        z-index: 9999;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #legend {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        font-size: 14px;
    }

    #legend b {
        display: block;
        margin-bottom: 10px;
        font-size: 16px;
    }

    #legend span {
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 8px;
        vertical-align: middle;
    }

    #legend div {
        margin: 5px 0;
    }
</style>
@endsection

@section('content')

<div class="flex gap-4">
    <div class="flex-1">
        <h1 class="text-xl font-bold mb-4">Peta Kesesuaian Lahan</h1>
        <div id="map" class="w-full" style="height: 600px;"></div>
    </div>

    {{-- LEGEND SIDEBAR --}}
    <div id="legend" style="width: 200px; height: fit-content;">
        <b>Legenda</b>
        <div><span style="background:#00aa00;"></span> S1 (Sangat Sesuai)</div>
        <div><span style="background:#d4d40d;"></span> S2 (Cukup Sesuai)</div>
        <div><span style="background:#ff8800;"></span> S3 (Sesuai Marginal)</div>
        <div><span style="background:#cc0000;"></span> N (Tidak Sesuai)</div>
    </div>
</div>

<div id="loading">
    <div class="spinner"></div>
    <p>Memuat data geometri...</p>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// ================== BASE MAPS =====================
var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OSM'
});

var esriSat = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 
    { attribution: 'Tiles © Esri' }
);

var terrain = L.tileLayer(
    'https://{s}.tile.stamen.com/terrain/{z}/{x}/{y}.jpg', 
    { attribution: 'Map tiles by Stamen Terrain' }
);

// ================== INISIALISASI MAP =====================
var map = L.map('map', {
    center: [-6.2, 106.15],
    zoom: 11,
    layers: [osm]
});

// ================== WARNA KELAS =====================
function warnaKelas(k) {
    return {
        'S1': '#00aa00',
        'S2': '#d4d40d',
        'S3': '#ff8800',
        'N':  '#cc0000'
    }[k] || '#999';
}

// ================== OVERLAY KESESUAIAN LAHAN =====================
var layerKesesuaian = L.layerGroup();

// Load GeoJSON dengan loading animation
document.getElementById('loading').style.display = 'block';

fetch("{{ route('map.geojson') }}")
    .then(r => r.json())
    .then(json => {
        var geoLayer = L.geoJSON(json, {
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
                        <b>Kelas Kesesuaian:</b> ${p.kelas_kesesuaian ?? '-'}<br><br>
                        <b>Skor Total:</b> ${p.nilai_total ?? '-'}<br>
                        <b>Ranking VIKOR:</b> ${p.vikor_ranking ?? '-'}<br>
                        <b>Q-Value:</b> ${p.vikor_q ?? '-'}<br>
                    </div>
                `);
            }
        });

        geoLayer.addTo(layerKesesuaian);
        layerKesesuaian.addTo(map);
        document.getElementById('loading').style.display = 'none';
    })
    .catch(err => {
        console.error('Error loading map:', err);
        document.getElementById('loading').style.display = 'none';
    });

// ================== LAYER CONTROL =====================
var baseMaps = {
    "OSM Standard": osm,
    "Esri Satellite": esriSat,
    "Stamen Terrain": terrain
};

var overlayMaps = {
    "Kesesuaian Lahan": layerKesesuaian
};

L.control.layers(baseMaps, overlayMaps, { collapsed: false }).addTo(map);
</script>
@endsection
