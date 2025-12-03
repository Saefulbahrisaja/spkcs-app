@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
    #map {
        width: 100%;
        height: 420px;
        border-radius: 6px;
    }

    .legend-box {
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
        font-size: 13px;
        line-height: 18px;
    }

    .legend-color {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 3px;
        margin-right: 6px;
    }
    /* Loading Geometri */
    /* Progress Loading GeoJSON */
#progress-box {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px 30px;
    border-radius: 8px;
    z-index: 9999;
    width: 280px;
    box-shadow: 0 0 12px rgba(0,0,0,0.2);
}

#progress-label {
    font-weight: 600;
    margin-bottom: 6px;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: #eee;
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    width: 0%;
    background: #007bff;
    transition: width 0.2s;
}
</style>
@endsection
@section('content')
<p>Selamat datang, {{ auth()->user()->nama }}</p>
<!-- ======================= RINGKASAN LUAS ======================= -->
<div class="row mb-4" id="ringkasanLuasContainer">
</div>
<!-- ======================= MAP SECTION ======================= -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-map me-2"></i>Peta Kesesuaian Lahan</h5>
    </div>

    <div class="card-body position-relative">
        <!-- Map -->
        <div id="progress-box" style="display:none;">
            <div id="progress-label">Memuat data luas  0%</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>
        <div id="map"></div>
    </div>
</div>

@endsection
@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// =============================
// ELEMENT PROGRESS BAR
// =============================
var box   = document.getElementById('progress-box');
var label = document.getElementById('progress-label');
var fill  = document.getElementById('progress-fill');

// Tampilkan progress box
box.style.display = "block";

let progress = 0;

// Set progress function
function setProgress(p, text) {
    progress = Math.min(100, p);
    fill.style.width = progress + "%";
    label.innerHTML = text + " " + Math.round(progress) + "%";
}

// =============================
// 1. LOAD RINGKASAN LUAS (0–40%)
// =============================
fetch("{{ route('admin.ringkasan.luas') }}")
    .then(r => r.json())
    .then(data => {

        let totalS1 = Object.values(data.S1 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalS2 = Object.values(data.S2 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalS3 = Object.values(data.S3 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalN  = Object.values(data.N  ?? {}).reduce((a,b)=>a+b,0) / 10000;

        document.getElementById('ringkasanLuasContainer').innerHTML = `
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-success shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-success fw-bold">Total Luas Sangat Sesuai</h6>
                        <div class="h5 mb-0">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalS1)}
 ha</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-warning fw-bold">Total Luas Cukup Sesuai</h6>
                        <div class="h5 mb-0">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalS2)}
 ha</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-orange shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-orange fw-bold">Total Luas Marginal</h6>
                        <div class="h5 mb-0">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalS3)}
 ha</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-danger shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-danger fw-bold">Total Luas Tidak Sesuai</h6>
                        <div class="h5 mb-0">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalN)}
 ha</div>
                    </div>
                </div>
            </div>
        `;
        // Progress selesai load luas → 40%
        setProgress(40, "Memuat data geometri");

    });

// =========================
// BASEMAPS
// =========================
var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: "© OpenStreetMap"
});
var esriSat = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", 
    { attribution: "Tiles © Esri" }
);

var map = L.map("map", {
    center: [-6.5, 106.16],
    zoom: 9,
    layers: [osm]
});

// =========================
// WARNA KELAS
// =========================
function warnaKelas(k) {
    return {
        S1: "#00aa00",
        S2: "#d4d40d",
        S3: "#f97316",
        N: "#cc0000"
    }[k] || "#999";
}

// =========================
// BUAT LAYER GROUP PER KELAS
// =========================
var layerS1 = L.layerGroup();
var layerS2 = L.layerGroup();
var layerS3 = L.layerGroup();
var layerN  = L.layerGroup();

// Untuk memetakan string ke layerGroup
var classLayers = {
    S1: layerS1,
    S2: layerS2,
    S3: layerS3,
    N:  layerN
};

// ELEMENT PROGRESS
var box   = document.getElementById('progress-box');
var label = document.getElementById('progress-label');
var fill  = document.getElementById('progress-fill');

// Tampilkan progress
box.style.display = "block";

fetch("{{ route('map.geojson') }}")
    .then(r => r.json())
    .then(json => {

        const total = json.features.length;
        let loaded = 0;

        L.geoJSON(json, {
            style: f => ({
                color: warnaKelas(f.properties.kelas_kesesuaian),
                weight: 2,
                fillOpacity: 0.45
            }),

            onEachFeature: (feature, layer) => {

                // Update progress 40–100%
                loaded++;
                let geoPercent = 40 + (loaded / total * 60);
                setProgress(geoPercent, "Memuat geometri");

                let p = feature.properties;
                layer.bindPopup(`
                    <strong>Lokasi:</strong> ${p.lokasi}<br>
                    <strong>Kelas:</strong> ${p.kelas_kesesuaian}<br>
                    <strong>Nilai Total:</strong> ${p.nilai_total}<br>
                    <strong>VIKOR Ranking:</strong> ${p.vikor_ranking}
                `);

                let kelas = p.kelas_kesesuaian || 'N';
                classLayers[kelas].addLayer(layer);
            }
        });

        layerS1.addTo(map);
        layerS2.addTo(map);
        layerS3.addTo(map);
        layerN.addTo(map);

        setTimeout(() => box.style.display = "none", 700);
    })
    .catch(err => {
        console.error(err);
        label.innerHTML = "Gagal memuat data/data tidak ada!";
    });

var baseMaps = {
    "OSM Standard": osm,
    "Esri Satellite": esriSat
};

var overlayMaps = {
    '<span style="display:inline-block;width:14px;height:14px;background:#00aa00;margin-right:6px;border-radius:2px;"></span> S1 - Sangat Sesuai': layerS1,
    '<span style="display:inline-block;width:14px;height:14px;background:#d4d40d;margin-right:6px;border-radius:2px;"></span> S2 - Cukup Sesuai': layerS2,
    '<span style="display:inline-block;width:14px;height:14px;background:#f97316;margin-right:6px;border-radius:2px;"></span> S3 - Marginal': layerS3,
    '<span style="display:inline-block;width:14px;height:14px;background:#cc0000;margin-right:6px;border-radius:2px;"></span> N - Tidak Sesuai': layerN
};

L.control.layers(baseMaps, overlayMaps, { collapsed: false }).addTo(map);
</script>
@endsection

