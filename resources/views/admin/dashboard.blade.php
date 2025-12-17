@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" rel="stylesheet"/>
<style>
    #map { 
        width: 100%; 
        height: 720px; 
        border-radius: 8px; 
        border: 1px solid #e6e6e6; 
        box-shadow: 0 4px 14px rgba(0,0,0,0.06); 
    }

    #mapLoading {
        position: absolute; 
        inset: 0; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center;
        background: rgba(255,255,255,0.86); 
        z-index: 2000; 
        border-radius: 8px;
    }

    #mapLoading .spinner { 
        width: 56px; 
        height: 56px; 
        border: 6px solid #e9e9e9; 
        border-top-color: #0d6efd; 
        border-radius: 50%; 
        animation: spin 0.9s linear infinite; 
    }

    #mapLoading .loading-text { 
        margin-top: 10px; 
        font-weight: 600; 
        color: #333; 
        font-size: 0.92rem; 
    }

    @keyframes spin { 
        to { transform: rotate(360deg); } 
    }

    #menuBtn {
        position: absolute; 
        left: 14px; 
        top: 14px; 
        z-index: 1300;
        width: 44px; 
        height: 44px; 
        border-radius: 8px; 
        background: #fff; 
        border: 1px solid #e6e6e6;
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    .modal-content { border-radius: 10px; }
    .small-muted { font-size: 0.88rem; color: #555; }
    .legend-swatch { 
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 3px;
        margin-right: 8px;
        vertical-align: middle; 
    }
    .details-card { 
        border-radius: 8px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.04); 
        padding: 12px; 
        background: #fff; 
    }

    .leaflet-popup-content-wrapper.custom-popup {
        border-radius: 10px;
        padding: 0;
        box-shadow: 0 4px 18px rgba(0,0,0,0.15);
    }

    .custom-popup-header {
        padding: 10px 14px;
        color: white;
        font-weight: 600;
        font-size: 14px;
        border-radius: 10px 10px 0 0;
    }

    .custom-popup-body {
        padding: 10px 14px;
        font-size: 13px;
    }

    .custom-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px solid #eee;
    }

    .custom-row:last-child { border-bottom: none; }
    .custom-label { color: #555; }
    .custom-value { font-weight: 600; }

    .attr-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 12px;
        font-size: 13px;
    }

    .attr-scroll-box {
        max-height: 140px;
        overflow-y: auto;
        margin-top: 6px;
        padding-right: 4px;
    }

    .attr-scroll-box::-webkit-scrollbar { width: 6px; }
    .attr-scroll-box::-webkit-scrollbar-track { 
        background: #f1f1f1; 
        border-radius: 3px; 
    }
    .attr-scroll-box::-webkit-scrollbar-thumb { 
        background: #b8b8b8; 
        border-radius: 3px; 
    }
    .attr-scroll-box::-webkit-scrollbar-thumb:hover { background: #888; }

    #rightLegend {
        position: absolute;
        top: 80px;
        right: 20px;
        width: 220px;
        background: #ffffff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px 14px;
        z-index: 1200;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        font-size: 0.85rem;
    }

    .leg-title {
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }

    .leg-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .leg-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: inline-block;
        margin-right: 8px;
    }

    #legendAtributList {
        max-height: 120px;
        overflow-y: auto;
        padding-right: 4px;
    }

    #legendAtributList::-webkit-scrollbar { width: 6px; }
    #legendAtributList::-webkit-scrollbar-track { 
        background: #f1f1f1; 
        border-radius: 3px; 
    }
    #legendAtributList::-webkit-scrollbar-thumb { 
        background: #b8b8b8; 
        border-radius: 3px; 
    }
</style>
@endsection

@section('content')
<div class="container-fluid position-relative">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Peta Kesesuaian Lahan — Versi Ringkas (Menu modal)</h4>
        <div class="d-flex gap-2">
            <button id="exportPNG" class="btn btn-sm btn-outline-primary">Export PNG</button>
            <button id="exportPDF" class="btn btn-sm btn-outline-secondary">Export PDF</button>
        </div>
    </div>

    <div class="row">
        <div class="col-12 position-relative">
            <div id="map"></div>

            <div id="rightLegend">
                <h6 class="leg-title">Legenda Kelas</h6>
                <div class="leg-item">
                    <input type="checkbox" class="kelas-check me-1" value="S1" checked>
                    <span class="leg-color" style="background:#00aa00"></span>S1 — Sangat Sesuai
                </div>
                <div class="leg-item">
                    <input type="checkbox" class="kelas-check me-1" value="S2" checked>
                    <span class="leg-color" style="background:#d4d40d"></span>S2 — Cukup Sesuai
                </div>
                <div class="leg-item">
                    <input type="checkbox" class="kelas-check me-1" value="S3" checked>
                    <span class="leg-color" style="background:#ff8800"></span>S3 — Marginal
                </div>
                <div class="leg-item">
                    <input type="checkbox" class="kelas-check me-1" value="N" checked>
                    <span class="leg-color" style="background:#cc0000"></span>N — Tidak Sesuai
                </div>
                <hr>
                <h6 class="leg-title">Legenda Atribut</h6>
                <div id="legendAtributList"></div>
            </div>

            <button id="menuBtn" class="btn" data-bs-toggle="modal" data-bs-target="#mapControlsModal" title="Open map controls">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12h18M3 6h18M3 18h18"/>
                </svg>
            </button>

            <div id="mapLoading" style="display:none;">
                <div class="spinner"></div>
                <div class="loading-text">Memuat data peta...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mapControlsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Kontrol Peta</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="mb-2">
                <label class="form-label small">Mode</label>
                <select id="viewModeSelect" class="form-select form-select-sm">
                    <option value="kelas" selected>Kelas</option>
                    <option value="atribut">Atribut</option>
                    <option value="gabungan">Gabungan</option>
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label small">Prioritas Layer</label>
                <select id="layerPriority" class="form-select form-select-sm">
                    <option value="atribut">Atribut di atas</option>
                    <option value="kelas">Kelas di atas</option>
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label small">Opacity Kelas</label>
                <input id="kelasOpacity" class="form-range" type="range" min="0" max="1" step="0.05" value="0.6">
            </div>

            <div class="mb-2">
                <label class="form-label small">Opacity Atribut</label>
                <input id="atributOpacity" class="form-range" type="range" min="0" max="1" step="0.05" value="0.8">
            </div>

            <div class="mb-2">
                <label class="form-label small">Intersect Analytic (pilih 2–3)</label>
                <select id="intersectAttrs" class="form-select form-select-sm" multiple size="3"></select>
                <div class="d-grid gap-2 mt-2">
                    <button id="runIntersect" class="btn btn-sm btn-success">Jalankan Intersect</button>
                    <button id="clearIntersect" class="btn btn-sm btn-outline-secondary">Bersihkan Intersect</button>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label small">Filter Atribut</label>
                <div id="atributChecks" class="legend-box" style="max-height:120px; overflow:auto;"></div>
            </div>

            <div class="text-end">
                <button class="btn btn-sm btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
// ============================================
// MAP CONFIGURATION & STATE
// ============================================
const classColors = { S1: '#00aa00', S2: '#d4d40d', S3: '#ff8800', N: '#cc0000', default: '#999' };
const kelasColorMap = { S1: '#00AA00', S2: '#D4D40D', S3: '#FF8800', N: '#CC0000' };

let geojsonData = null;
let classLayerGroup = L.layerGroup();
let attributeLayers = {};
let clusterGroup = L.markerClusterGroup({ chunkedLoading: true, chunkProgress: false });
let intersectLayerGroup = L.layerGroup();

// ============================================
// UTILITY FUNCTIONS
// ============================================
const showLoader = () => document.getElementById('mapLoading').style.display = 'flex';
const hideLoader = () => document.getElementById('mapLoading').style.display = 'none';
const generateColor = (i) => `hsl(${(i * 47) % 360}, 70%, 45%)`;
const formatLabel = (key) => key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
const ensurePolygon = (g) => !g ? null : (g.type === 'Polygon' || g.type === 'MultiPolygon' ? g : null);

// ============================================
// MAP INITIALIZATION
// ============================================
const map = L.map('map', { center: [-6.3, 106.16], zoom: 10, preferCanvas: true, zoomControl: true });
const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
const sat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });

L.control.layers({ "OSM": osm, "Satellite": sat }, {}, { collapsed: true }).addTo(map);
L.control.scale({ metric: true }).addTo(map);
intersectLayerGroup.addTo(map);

// ============================================
// FETCH DATA
// ============================================
async function fetchAttributes() {
    try {
        const r = await fetch("{{ route('map.atribut') }}");
        return r.ok ? await r.json() : [];
    } catch (e) {
        console.error(e);
        return [];
    }
}

async function fetchGeoJSON() {
    try {
        const r = await fetch("{{ route('map.geojson') }}");
        if (!r.ok) throw new Error('Failed to fetch GeoJSON');
        return await r.json();
    } catch (e) {
        console.error(e);
        throw e;
    }
}

// ============================================
// UI BUILDERS
// ============================================
function buildAtributChecks(attrs) {
    const container = document.getElementById('atributChecks');
    const interSel = document.getElementById('intersectAttrs');
    container.innerHTML = '';
    interSel.innerHTML = '';

    attrs.forEach((a, i) => {
        const id = 'attr_cb_' + i;
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input attr-check" type="checkbox" id="${id}" value="${a}">
            <label class="form-check-label small" for="${id}">${a}</label>
        `;
        container.appendChild(div);

        const option = document.createElement('option');
        option.value = a;
        option.textContent = a;
        interSel.appendChild(option);
    });
}

function buildLegendAtribut(attrs) {
    const box = document.getElementById("legendAtributList");
    box.innerHTML = "";

    attrs.forEach((a, i) => {
        const color = generateColor(i);
        const div = document.createElement("div");
        div.className = "leg-item";
        div.innerHTML = `<span class="leg-color" style="background:${color}"></span>${a}`;
        box.appendChild(div);
    });
}

// ============================================
// STYLE & FILTERING
// ============================================
function getEnabledClasses() {
    return Array.from(document.querySelectorAll('.kelas-check'))
        .filter(cb => cb.checked)
        .map(cb => cb.value);
}

function styleKelas(feature) {
    const kelas = feature.properties?.kelas_kesesuaian ?? feature.properties?.kelas ?? 'N';
    return {
        color: classColors[kelas] ?? classColors.default,
        weight: 1,
        fillOpacity: parseFloat(document.getElementById('kelasOpacity').value || 0.6),
        fillColor: classColors[kelas] ?? classColors.default
    };
}

// ============================================
// LAYER PREPARATION
// ============================================
function prepareLayers(fc, attributes) {
    geojsonData = fc;
    classLayerGroup.clearLayers();
    clusterGroup.clearLayers();
    attributeLayers = {};

    const kelasGeo = L.geoJSON(fc, {
        filter: (feature) => {
            const allowed = getEnabledClasses();
            const k = feature.properties?.kelas_kesesuaian ?? feature.properties?.kelas ?? 'N';
            return allowed.includes(k);
        },
        style: styleKelas,
        pointToLayer: (feature, latlng) => {
            const kelas = feature.properties?.kelas_kesesuaian ?? feature.properties?.kelas;
            return L.circleMarker(latlng, { 
                radius: 5, 
                fillOpacity: 0.9, 
                weight: 0, 
                fillColor: classColors[kelas] || classColors.default 
            });
        },
        onEachFeature: (feature, layer) => {
            layer.on({
                click: (e) => showDetail(feature.properties, e.target),
                mouseover: (e) => { if (e.target.setStyle) e.target.setStyle({ weight: 2 }); },
                mouseout: (e) => { if (e.target.setStyle) e.target.setStyle({ weight: 1 }); }
            });
        }
    });

    classLayerGroup.addLayer(kelasGeo).addTo(map);

    const points = fc.features.filter(f => f.geometry && f.geometry.type === 'Point');
    if (points.length > 80) {
        const markers = L.featureGroup();
        points.forEach(p => {
            const coords = p.geometry.coordinates;
            const kelas = p.properties?.kelas_kesesuaian || p.properties?.kelas;
            const mk = L.circleMarker([coords[1], coords[0]], { 
                radius: 5, 
                fillColor: classColors[kelas] || classColors.default, 
                fillOpacity: 0.9, 
                weight: 0 
            });
            mk.on('click', () => showDetail(p.properties, mk));
            markers.addLayer(mk);
        });
        clusterGroup.addLayer(markers).addTo(map);
    }

    attributes.forEach((a, idx) => {
        attributeLayers[a] = L.geoJSON(null, { 
            style: { 
                color: generateColor(idx), 
                weight: 1, 
                fillOpacity: parseFloat(document.getElementById('atributOpacity').value || 0.8) 
            } 
        });
    });

    try {
        const b = kelasGeo.getBounds();
        if (b.isValid && b.isValid()) map.fitBounds(b, { padding: [8, 8], maxZoom: 15 });
    } catch (e) {
        console.warn(e);
    }

    hideLoader();
}


async function ambilRekomendasiDinas(alternatifId) {
    try {
        const res = await fetch(`/api/rekomendasi/${alternatifId}`);
        if (!res.ok) throw new Error('Gagal ambil rekomendasi');

        const data = await res.json();
        return data.rekomendasi || 'Rekomendasi belum tersedia.';
    } catch (e) {
        console.error(e);
        return 'Rekomendasi tidak dapat dimuat.';
    }
}


function showDetail(props, layer) {
    const kelas = props.kelas_kesesuaian ?? props.kelas ?? 'N';
    const warnaHeader = kelasColorMap[kelas] ?? "#666";
    const rekom = ambilRekomendasiDinas(kelas);

    let attrHTML = "";
    const excludeKeys = ['lokasi', 'kelas_kesesuaian', 'nilai_total', 'vikor_q', 'vikor_v', 'alternatif_id', 'vikor_ranking', 'skor_normalisasi'];
    
    Object.keys(props).forEach(k => {
        if (excludeKeys.includes(k) || props[k] === null || props[k] === "") return;
        attrHTML += `
            <div class="custom-row">
                <span class="custom-label">${formatLabel(k)}</span>
                <span class="custom-value">${props[k]}</span>
            </div>
        `;
    });

    const html = `
        <div class="custom-popup-content">
            <div class="custom-popup-header" style="background:${warnaHeader}">${props.lokasi ?? 'Wilayah'}</div>
            <div class="custom-popup-body">
                <div class="custom-row">
                    <span class="custom-label">Kelas Kesesuaian</span>
                    <span class="custom-value">${kelas}</span>
                </div>
                <hr>
                <strong>Sampel Kriteria</strong>
                <div class="attr-scroll-box" style="margin-top:6px;">${attrHTML}</div>
                <hr>
                <strong style="color:${warnaHeader};">Direkomendasikan:</strong>
                <div class="mt-1" id="rekom-${props.alternatif_id}" class="small-muted">
                    Memuat rekomendasi...
                </div>
            </div>
        </div>
    `;

    layer.bindPopup(html, { maxWidth: 340, className:'custom-popup' }).openPopup();

    ambilRekomendasiDinas(props.alternatif_id)
        .then(text => {
            const el = document.getElementById(`rekom-${props.alternatif_id}`);
            if (el) el.innerHTML = `<div ><em> ${text}</em></div>`;
        });
}

// ============================================
// VIEW MODE & OPACITY HANDLERS
// ============================================
function updateViewModeFromModal() {
    const mode = document.getElementById('viewModeSelect').value;
    const priority = document.getElementById('layerPriority').value;

    Object.values(attributeLayers).forEach(l => map.removeLayer(l));
    map.removeLayer(classLayerGroup);

    if (mode === 'kelas') {
        classLayerGroup.addTo(map);
        if (clusterGroup.getLayers().length) clusterGroup.addTo(map);
    } else if (mode === 'atribut') {
        document.querySelectorAll('.attr-check').forEach(cb => {
            if (cb.checked && attributeLayers[cb.value]) attributeLayers[cb.value].addTo(map);
        });
    } else { // gabungan
        if (priority === 'atribut') {
            classLayerGroup.addTo(map);
        }
        document.querySelectorAll('.attr-check').forEach(cb => {
            if (cb.checked && attributeLayers[cb.value]) attributeLayers[cb.value].addTo(map);
        });
        if (priority === 'kelas') classLayerGroup.addTo(map);
    }
}

document.getElementById('kelasOpacity').addEventListener('input', () => {
    const v = parseFloat(document.getElementById('kelasOpacity').value);
    classLayerGroup.eachLayer(l => { if (l.setStyle) l.setStyle({ fillOpacity: v }); });
});

document.getElementById('atributOpacity').addEventListener('input', () => {
    const v = parseFloat(document.getElementById('atributOpacity').value);
    Object.values(attributeLayers).forEach(al => {
        if (al && al.eachLayer) al.eachLayer(l => { if (l.setStyle) l.setStyle({ fillOpacity: v }); });
    });
});

// ============================================
// ATTRIBUTE TOGGLE (LAZY LOAD)
// ============================================
document.addEventListener('change', (e) => {
    if (!e.target.classList?.contains('attr-check')) return;
    
    const attr = e.target.value;
    if (!geojsonData) return;

    if (e.target.checked) {
        if (!attributeLayers[attr] || Object.keys(attributeLayers[attr]._layers || {}).length === 0) {
            const filtered = geojsonData.features.filter(f => {
                const p = f.properties || {};
                return (p[attr] !== undefined && p[attr] !== null) || Object.keys(p).some(k => k.toLowerCase() === attr.toLowerCase());
            });
            attributeLayers[attr] = L.geoJSON(filtered, {
                style: {
                    color: generateColor(Object.keys(attributeLayers).indexOf(attr) || 0),
                    weight: 1,
                    fillOpacity: parseFloat(document.getElementById('atributOpacity').value || 0.8)
                }
            });
        }
        attributeLayers[attr].addTo(map);
    } else {
        if (attributeLayers[attr]) map.removeLayer(attributeLayers[attr]);
    }
});

document.addEventListener('change', (e) => {
    if (!e.target.classList?.contains('kelas-check')) return;
    prepareLayers(geojsonData, Object.keys(attributeLayers));
    updateViewModeFromModal();
});

// ============================================
// INTERSECT ANALYSIS
// ============================================
function runIntersectAnalysis(attrList) {
    intersectLayerGroup.clearLayers();
    if (!geojsonData) return alert("GeoJSON belum dimuat.");

    const attrFeatures = attrList.map(attr =>
        geojsonData.features.filter(f => {
            const p = f.properties || {};
            return (p.atribut === attr) || (p[attr] !== undefined) || Object.keys(p).some(k => k.toLowerCase() === attr.toLowerCase());
        })
    );

    if (attrFeatures.some(a => a.length === 0)) return alert("Ada atribut tanpa polygon.");

    const g1 = attrFeatures[0].map(f => ensurePolygon(f.geometry)).filter(Boolean);
    const g2 = attrFeatures[1].map(f => ensurePolygon(f.geometry)).filter(Boolean);
    let mid = [];

    g1.forEach(A => g2.forEach(B => {
        try {
            const inter = turf.intersect(turf.feature(A), turf.feature(B));
            if (inter) mid.push(inter);
        } catch (e) { }
    }));

    let finalRes = mid;
    if (attrList.length === 3) {
        const g3 = attrFeatures[2].map(f => ensurePolygon(f.geometry)).filter(Boolean);
        let temp = [];
        mid.forEach(M => g3.forEach(C => {
            try {
                const inter2 = turf.intersect(M, turf.feature(C));
                if (inter2) temp.push(inter2);
            } catch (e) { }
        }));
        finalRes = temp;
    }

    if (!finalRes || finalRes.length === 0) return alert("Tidak ada area tumpang tindih.");

    finalRes.forEach((ft, i) => {
        const color = `hsl(${(i * 71) % 360}, 75%, 45%)`;
        const layer = L.geoJSON(ft, { style: { color, weight: 3, fillOpacity: 0.55 } }).addTo(intersectLayerGroup);
        layer.on('click', () => {
            const luasHa = (turf.area(ft) / 10000).toFixed(2);
            console.log(`Luas: ${luasHa} ha`);
        });
    });

    alert("Intersect selesai — cek area berwarna.");
}

document.getElementById('runIntersect').addEventListener('click', () => {
    const selected = Array.from(document.getElementById('intersectAttrs').selectedOptions).map(o => o.value);
    if (selected.length < 2 || selected.length > 3) return alert("Pilih 2 atau 3 atribut.");
    runIntersectAnalysis(selected);
});

document.getElementById('clearIntersect').addEventListener('click', () => {
    intersectLayerGroup.clearLayers();
});

// ============================================
// INITIALIZATION
// ============================================
(async function init() {
    try {
        showLoader();
        const [attrs, fc] = await Promise.all([fetchAttributes(), fetchGeoJSON()]);
        buildAtributChecks(attrs || []);
        buildLegendAtribut(attrs);
        prepareLayers(fc, attrs || []);
        geojsonData = fc;

        const modal = document.getElementById('mapControlsModal');
        modal.addEventListener('hidden.bs.modal', updateViewModeFromModal);
        document.getElementById('viewModeSelect').addEventListener('change', updateViewModeFromModal);
        document.getElementById('layerPriority').addEventListener('change', updateViewModeFromModal);
    } catch (err) {
        console.error(err);
        alert("Gagal memuat data peta. Periksa endpoint server.");
    } finally {
        hideLoader();
    }
})();

// ============================================
// EXPORT HANDLERS
// ============================================
document.getElementById('exportPNG').addEventListener('click', () => {
    alert('Export PNG: gunakan browser print atau integrate leaflet-easyPrint jika diperlukan.');
});

document.getElementById('exportPDF').addEventListener('click', () => window.print());
</script>
@endsection
