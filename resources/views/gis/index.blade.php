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
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .spinner {
        border: 4px solid #ddd;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Peta Kesesuaian Lahan</li>
</ol>

<div class="row">

    <!-- MAP AREA -->
    <div class="col-md-9 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>Peta Kesesuaian Lahan</h5>
            </div>
            <div class="card-body p-0">
                <div id="map" style="width: 100%; height: 600px; border-radius: 0 0 6px 6px;"></div>
            </div>
        </div>
    </div>

    <!-- LEGEND -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Legenda</strong>
            </div>
            <div class="card-body">

                <div class="d-flex align-items-center mb-2">
                    <span class="me-2" style="width:20px; height:20px; background:#00aa00; border-radius:4px;"></span>
                    S1 — Sangat Sesuai
                </div>

                <div class="d-flex align-items-center mb-2">
                    <span class="me-2" style="width:20px; height:20px; background:#d4d40d; border-radius:4px;"></span>
                    S2 — Cukup Sesuai
                </div>

                <div class="d-flex align-items-center mb-2">
                    <span class="me-2" style="width:20px; height:20px; background:#ff8800; border-radius:4px;"></span>
                    S3 — Sesuai Marginal
                </div>

                <div class="d-flex align-items-center">
                    <span class="me-2" style="width:20px; height:20px; background:#cc0000; border-radius:4px;"></span>
                    N — Tidak Sesuai
                </div>

            </div>
        </div>
    </div>

</div>

<!-- LOADING -->
<div id="loading">
    <div class="spinner mb-3"></div>
    <p class="mb-0">Memuat data geometri...</p>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-easyprint/dist/bundle.min.js"></script>

<script>
// ========== BASEMAPS ==========
var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '© OSM'
});

var esriSat = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { attribution: 'Tiles © Esri' }
);

var terrain = L.tileLayer(
    'https://{s}.tile.stamen.com/terrain/{z}/{x}/{y}.jpg',
    { attribution: 'Stamen Terrain' }
);

// ========== INITIALIZE MAP ==========
var map = L.map('map', {
    center: [-6.5, 106.16],
    zoom: 9,
    layers: [osm]
});

// ========== CLASS COLORS ==========
function warnaKelas(k) {
    return {
        'S1': '#00aa00',
        'S2': '#d4d40d',
        'S3': '#ff8800',
        'N':  '#cc0000'
    }[k] || '#999';
}

// ========== LOAD GEOJSON ==========
var layerKesesuaian = L.layerGroup();
document.getElementById('loading').style.display = 'block';

fetch("{{ route('map.geojson') }}")
    .then(r => r.json())
    .then(json => {
        L.geoJSON(json, {
            style: f => ({
                color: warnaKelas(f.properties.kelas_kesesuaian),
                weight: 2,
                fillOpacity: 0.5
            }),
            onEachFeature: (feature, layer) => {
                let p = feature.properties;
                layer.bindPopup(`
                    <div>
                        <strong>Lokasi:</strong> ${p.lokasi}<br>
                        <strong>Kelas:</strong> ${p.kelas_kesesuaian ?? '-'}<br>
                        <strong>Skor Total:</strong> ${p.nilai_total ?? '-'}<br>
                        <strong>Ranking VIKOR:</strong> ${p.vikor_ranking ?? '-'}<br>
                        <strong>Q-Value:</strong> ${p.vikor_q ?? '-'}
                    </div>
                `);
            }
        }).addTo(layerKesesuaian);

        layerKesesuaian.addTo(map);
        document.getElementById('loading').style.display = 'none';
    })
    .catch(err => {
        console.error(err);
        document.getElementById('loading').style.display = 'none';
    });

// ========== LAYERS CONTROL ==========
L.control.layers(
    {
        "OSM Standard": osm,
        "Esri Satellite": esriSat,
        "Stamen Terrain": terrain
    },
    { "Kesesuaian Lahan": layerKesesuaian },
    { collapsed: false }
).addTo(map);

</script>
@endsection
