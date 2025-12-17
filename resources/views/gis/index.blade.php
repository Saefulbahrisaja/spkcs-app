@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-minimap/dist/Control.MiniMap.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/leaflet-easyprint@2.1.9/libs/leaflet.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder@3.3.1/dist/Control.Geocoder.min.css" rel="stylesheet">

<style>
  /* ========== MAP AREA ========== */
  #map {
    width: 100%;
    height: 640px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }

  /* ========== FLOATING SIDEBAR ========== */
  #sidebar {
    position: absolute;
    top: 75px;
    left: 20px;
    width: 250px;
    max-height: 75vh;
    overflow-y: auto;
    z-index: 1200;
    padding: 16px;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #dee2e6;
  }

  #sidebar hr {
    margin: 14px 0;
    border: none;
    border-top: 1px solid #e9ecef;
  }

  /* ========== TITLES & LABELS ========== */
  #sidebar h6 {
    font-weight: 600;
    margin-bottom: 10px;
    color: #212529;
    font-size: 0.95rem;
  }

  .form-label.small {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
  }

  /* ========== MODE TOGGLE ========== */
  .mode-toggle {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
  }

  .mode-toggle label {
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #495057;
  }

  .mode-toggle label input {
    margin-right: 8px;
  }

  /* ========== CHECKBOX AREA ========== */
  .legend-box {
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding-left: 10px;
    max-height: 100px;
    overflow-y: auto;
  }

  .legend-box .form-check {
    padding: auto;
  }

  .legend-box .form-check-label {
    font-size: 0.8rem;
    cursor: pointer;
  }

  /* ========== ATTRIBUTE LEGEND ========== */
  #attrLegend div {
    padding: 6px 0;
    font-size: 0.8rem;
    color: #495057;
    display: flex;
    align-items: center;
  }

  /* ========== RIGHT SIDEBAR DETAIL PANEL ========== */
  #detailBox {
    min-height: 140px;
    font-size: 0.875rem;
  }

  #detailBox h5 {
    font-weight: 600;
    margin-bottom: 12px;
    color: #212529;
    font-size: 1rem;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.8rem;
  }

  .detail-row:last-child {
    border-bottom: none;
  }

  .detail-row strong {
    color: #212529;
    font-weight: 600;
  }

  /* ========== FORM CONTROLS ========== */
  .form-select-sm,
  .form-range {
    font-size: 0.875rem;
  }

  .form-select {
    border: 1px solid #dee2e6;
    border-radius: 4px;
  }

  .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  .form-range {
    height: 4px;
  }

  /* ========== BUTTONS ========== */
  .btn-block {
    width: 100%;
  }

  button.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  button.btn-sm:hover {
    transform: translateY(-1px);
  }

  .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  /* ========== SCROLLBAR ========== */
  #sidebar::-webkit-scrollbar,
  .legend-box::-webkit-scrollbar {
    width: 6px;
  }

  #sidebar::-webkit-scrollbar-track,
  .legend-box::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 3px;
  }

  #sidebar::-webkit-scrollbar-thumb,
  .legend-box::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 3px;
  }

  #sidebar::-webkit-scrollbar-thumb:hover,
  .legend-box::-webkit-scrollbar-thumb:hover {
    background: #868e96;
  }

  /* ========== CARD IMPROVEMENTS ========== */
  .card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
  }

  .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    padding: 12px 16px;
  }

  .card-body {
    padding: 14px 16px;
  }

  /* ========== RESPONSIVE ========== */
  @media (max-width: 1024px) {
    #sidebar {
      width: 220px;
      top: 70px;
      left: 15px;
    }
  }

  @media (max-width: 768px) {
    #sidebar {
      position: fixed;
      width: 280px;
      top: 60px;
      left: 10px;
      right: auto;
      max-height: calc(100vh - 80px);
    }

    #map {
      height: 500px;
    }
  }

  @media (max-width: 480px) {
    #sidebar {
      width: calc(100% - 30px);
      max-height: 50vh;
    }
  }
</style>
@endsection

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Peta Kesesuaian Lahan</h4>
    <div class="d-flex gap-2">
      <button id="exportPNG" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-image"></i> Export PNG
      </button>
      <button id="exportPDF" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-file-pdf"></i> Export PDF
      </button>
    </div>
  </div>

  <div class="row g-3">
    <!-- MAP AREA -->
    <div class="col-lg-9 position-relative">
      <div id="map"></div>

      <!-- FLOATING SIDEBAR -->
      <div id="sidebar">
        <!-- MODE -->
        <h6>Mode Tampilan</h6>
        <div class="mode-toggle">
          <label><input type="radio" name="viewMode" value="kelas" checked> Kelas</label>
          <label><input type="radio" name="viewMode" value="atribut"> Atribut</label>
          <label><input type="radio" name="viewMode" value="gabungan"> Gabungan</label>
        </div>

        <hr>

        <!-- PRIORITY -->
        <label class="form-label small">Prioritas Layer</label>
        <select id="layerPriority" class="form-select form-select-sm mb-3">
          <option value="atribut">Atribut di atas</option>
          <option value="kelas">Kelas di atas</option>
        </select>

        <hr>

        <!-- INTERSECT ANALYTIC -->
        <div>
          <label class="form-label small">Intersect Analytic (Pilih 2–3 atribut)</label>
          <select id="intersectAttrs" class="form-select form-select-sm" multiple size="3"></select>
          <button id="runIntersect" class="btn btn-sm btn-success btn-block mt-2">Jalankan Intersect</button>
          <button id="clearIntersect" class="btn btn-sm btn-outline-secondary btn-block mt-2">Bersihkan Intersect</button>
        </div>

        <hr>

        <!-- OPACITY -->
        <label class="form-label small">Opacity Kelas</label>
        <input id="kelasOpacity" type="range" min="0" max="1" step="0.05" value="0.6" class="form-range mb-3">

        <label class="form-label small">Opacity Atribut</label>
        <input id="atributOpacity" type="range" min="0" max="1" step="0.05" value="0.8" class="form-range mb-3">

        <!-- FILTER ATRIBUT -->
        <label class="form-label small">Filter Atribut</label>
        <div id="atributChecks" class="legend-box"></div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="col-lg-3">
      <!-- DETAIL PANEL -->
      <div class="card shadow-sm mt-3">
        <div class="card-header">Detail Polygon</div>
        <div class="card-body small" id="detailBox">
          <em class="text-muted">Klik polygon untuk melihat detail...</em>
        </div>
      </div>

      <!-- LEGEND PANEL -->
      <div class="card shadow-sm">
        <div class="card-header">Legenda Kelas</div>
        <div class="card-body small">
          <div style="margin-bottom: 8px;">
            <span style="display:inline-block;width:16px;height:16px;background:#00aa00;border-radius:2px;margin-right:8px"></span>
            S1 — Sangat Sesuai
          </div>
          <div style="margin-bottom: 8px;">
            <span style="display:inline-block;width:16px;height:16px;background:#d4d40d;border-radius:2px;margin-right:8px"></span>
            S2 — Cukup Sesuai
          </div>
          <div style="margin-bottom: 8px;">
            <span style="display:inline-block;width:16px;height:16px;background:#ff8800;border-radius:2px;margin-right:8px"></span>
            S3 — Marginal
          </div>
          <div style="margin-bottom: 12px;">
            <span style="display:inline-block;width:16px;height:16px;background:#cc0000;border-radius:2px;margin-right:8px"></span>
            N — Tidak Sesuai
          </div>

          <hr style="margin: 12px 0;">

          <strong style="font-size: 0.85rem;">Atribut</strong>
          <div id="attrLegend" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet & plugins -->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-minimap@3.6.1/dist/Control.MiniMap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-easyprint@2.1.9/dist/bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet-control-geocoder@3.3.1/dist/Control.Geocoder.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
  // ========= INIT MAP & BASE LAYERS =========
  const map = L.map('map', { center: [-6.3, 106.16], zoom: 10 });
  const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
  const sat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');
  const terrain = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png');

  L.control.layers(
    { "OSM": osm, "Satellite": sat, "TopoMap": terrain }
  ).addTo(map);

  // ========= FIX MINIMAP =========
  const miniLayer = L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { attribution: '' }
  );

  const mini = new L.Control.MiniMap(miniLayer, {
    toggleDisplay: true,
    minimized: false
  }).addTo(map);

  // ========= CONTROLS =========
  const printer = L.easyPrint({
    title: 'Print map',
    position: 'topleft',
    elementsToHide: '.leaflet-control-zoom'
  }).addTo(map);

  L.control.scale().addTo(map);
  const geocoder = L.Control.geocoder({ placeholder: 'Cari alamat/lokasi...' }).addTo(map);

  // ========= DATA CONTAINERS =========
  let geojsonData = null;
  let classLayerGroup = L.layerGroup().addTo(map);
  let attributeLayers = {};
  let clusterGroup = L.markerClusterGroup();
  let intersectLayerGroup = L.layerGroup().addTo(map);

  const classColors = {
    S1: '#00aa00',
    S2: '#d4d40d',
    S3: '#ff8800',
    N: '#cc0000',
    default: '#999'
  };

  // ========= FETCH DATA =========
  async function fetchAttributes() {
    const r = await fetch("{{ route('map.atribut') }}");
    return await r.json();
  }

  async function fetchGeoJSON() {
    const r = await fetch("{{ route('map.geojson') }}");
    return await r.json();
  }

  // ========= BUILD UI =========
  function buildAtributChecks(attrs) {
    const container = document.getElementById('atributChecks');
    container.innerHTML = '';

    attrs.forEach((a, i) => {
      const id = 'attr_cb_' + i;
      const div = document.createElement('div');
      div.className = 'form-check';
      div.innerHTML = `<input class="form-check-input attr-check" type="checkbox" id="${id}" value="${a}">
               <label class="form-check-label" for="${id}">${a}</label>`;
      container.appendChild(div);
    });

    const attrLegend = document.getElementById('attrLegend');
    attrLegend.innerHTML = '';
    attrs.forEach((a, i) => {
      const col = generateColor(i);
      const span = document.createElement('div');
      span.innerHTML = `<span style="display:inline-block;width:16px;height:16px;background:${col};border-radius:2px;margin-right:8px"></span>${a}`;
      attrLegend.appendChild(span);
    });

    const interSel = document.getElementById('intersectAttrs');
    attrs.forEach(a => {
      const o = document.createElement('option');
      o.value = a;
      o.textContent = a;
      interSel.appendChild(o);
    });
  }

  function generateColor(i) {
    return `hsl(${(i * 47) % 360}, 70%, 45%)`;
  }

  // ========= PREPARE LAYERS =========
  function prepareLayers(fc, attributes) {
    geojsonData = fc;
    classLayerGroup.clearLayers();
    Object.values(attributeLayers).forEach(lg => lg.clearLayers());
    attributeLayers = {};
    clusterGroup.clearLayers();

    attributes.forEach((a, i) => {
      attributeLayers[a] = L.layerGroup();
      attributeLayers[a].attrColor = generateColor(i);
    });

    fc.features.forEach(f => {
      const props = f.properties || {};
      const geom = f.geometry;
      if (!geom) return;

      const kelas = props.kelas_kesesuaian ?? props.kelas ?? 'N';
      const kelasColor = classColors[kelas] ?? classColors.default;

      let layer;
      if (geom.type === 'Point' || geom.type === 'MultiPoint') {
        const coords = geom.coordinates;
        const latlng = (geom.type === 'Point') ? [coords[1], coords[0]] : [coords[1][0], coords[0][0]];
        layer = L.circleMarker(latlng, {
          radius: 7,
          color: kelasColor,
          fillColor: kelasColor,
          fillOpacity: 0.8
        });
        clusterGroup.addLayer(layer);
      } else {
        layer = L.geoJSON(f, {
          style: () => ({
            color: kelasColor,
            weight: 2,
            fillOpacity: parseFloat(document.getElementById('kelasOpacity').value)
          })
        });
      }

      layer.on('click', () => showDetail(props, f));
      layer.on('mouseover', (e) => {
        e.target.setStyle && e.target.setStyle({ weight: 4 });
        const tip = `<strong>${props.lokasi ?? '-'}</strong><br>${kelas} • Q:${props.vikor_q ?? '-'}`;
        layer.bindTooltip(tip, { sticky: true }).openTooltip();
      });
      layer.on('mouseout', (e) => {
        e.target.setStyle && e.target.setStyle({ weight: 2 });
      });

      classLayerGroup.addLayer(layer);

      attributes.forEach(a => {
        let val = props[a] ?? props['atribut_' + a] ?? props.atribut ?? props.atribut_nama ?? props[a.toLowerCase()];
        if (val === undefined && typeof props === 'object') {
          const foundKey = Object.keys(props).find(k => k.toLowerCase() === a.toLowerCase());
          if (foundKey) val = props[foundKey];
        }
        if (val === undefined || val === null) return;

        const color = attributeLayers[a].attrColor;
        let attrLayer = L.geoJSON(f, {
          style: () => ({
            color: color,
            weight: 2,
            fillOpacity: parseFloat(document.getElementById('atributOpacity').value)
          })
        });

        attrLayer.on('click', () => showDetail(props, f));
        attributeLayers[a].addLayer(attrLayer);
      });
    });

    classLayerGroup.addTo(map);
    if (clusterGroup.getLayers().length) clusterGroup.addTo(map);
    map.invalidateSize();
  }

  // ========= SHOW DETAIL =========
  function showDetail(props, feature) {
    const box = document.getElementById('detailBox');
    box.innerHTML = '';

    const h = document.createElement('h5');
    h.textContent = props.lokasi ?? '—';
    box.appendChild(h);

    const rows = [
      ['Kelas', props.kelas_kesesuaian ?? '-'],
      ['Skor Total', props.nilai_total ?? '-'],
      ['Ranking VIKOR', props.vikor_ranking ?? '-'],
      ['Q-Value', props.vikor_q ?? '-']
    ];

    rows.forEach(r => {
      const div = document.createElement('div');
      div.className = 'detail-row';
      div.innerHTML = `<div>${r[0]}</div><div><strong>${r[1]}</strong></div>`;
      box.appendChild(div);
    });

    const title = document.createElement('div');
    title.className = 'mt-3 mb-2';
    title.innerHTML = '<strong>Atribut:</strong>';
    box.appendChild(title);

    const ul = document.createElement('div');
    Object.keys(props).forEach(k => {
      if (['lokasi', 'kelas_kesesuaian', 'nilai_total', 'vikor_q', 'vikor_v', 'alternatif_id', 'vikor_ranking', 'skor_normalisasi'].includes(k)) return;
      const pval = props[k];
      const row = document.createElement('div');
      row.className = 'detail-row';
      row.innerHTML = `<div>${k}</div><div>${(pval === null || pval === undefined) ? '-' : pval}</div>`;
      ul.appendChild(row);
    });
    box.appendChild(ul);
  }

  // ========= EVENT HANDLERS =========
  document.querySelectorAll('input[name="viewMode"]').forEach(r => {
    r.addEventListener('change', () => updateViewMode());
  });
  document.getElementById('layerPriority').addEventListener('change', () => updateViewMode());

  document.getElementById('kelasOpacity').addEventListener('input', () => {
    classLayerGroup.eachLayer(l => {
      if (l.setStyle) l.setStyle({ fillOpacity: parseFloat(document.getElementById('kelasOpacity').value) });
    });
  });

  document.getElementById('atributOpacity').addEventListener('input', () => {
    Object.keys(attributeLayers).forEach(a => {
      attributeLayers[a].eachLayer(l => {
        if (l.setStyle) l.setStyle({ fillOpacity: parseFloat(document.getElementById('atributOpacity').value) });
      });
    });
  });

  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('attr-check')) {
      const attr = e.target.value;
      if (e.target.checked) {
        attributeLayers[attr] && attributeLayers[attr].addTo(map);
      } else {
        attributeLayers[attr] && map.removeLayer(attributeLayers[attr]);
      }
    }
  });

  // ========= UPDATE VIEW MODE =========
  function updateViewMode() {
    const mode = document.querySelector('input[name="viewMode"]:checked').value;
    const priority = document.getElementById('layerPriority').value;

    map.removeLayer(classLayerGroup);
    Object.values(attributeLayers).forEach(l => map.removeLayer(l));

    if (mode === 'kelas') {
      classLayerGroup.addTo(map);
      clusterGroup.addTo(map);
    } else if (mode === 'atribut') {
      document.querySelectorAll('.attr-check').forEach(cb => {
        if (cb.checked) attributeLayers[cb.value] && attributeLayers[cb.value].addTo(map);
      });
      map.removeLayer(classLayerGroup);
    } else {
      if (priority === 'atribut') {
        classLayerGroup.addTo(map);
        document.querySelectorAll('.attr-check').forEach(cb => {
          if (cb.checked) attributeLayers[cb.value] && attributeLayers[cb.value].addTo(map);
        });
        Object.values(attributeLayers).forEach(lg => lg.eachLayer(layer => layer.bringToFront && layer.bringToFront()));
      } else {
        document.querySelectorAll('.attr-check').forEach(cb => {
          if (cb.checked) attributeLayers[cb.value] && attributeLayers[cb.value].addTo(map);
        });
        classLayerGroup.addTo(map);
        classLayerGroup.eachLayer(l => l.bringToFront && l.bringToFront());
      }
    }
  }

  // ========= EXPORT =========
  document.getElementById('exportPNG').addEventListener('click', () => {
    printer.printMap('CurrentSize', 'ExportedMap');
  });

  document.getElementById('exportPDF').addEventListener('click', () => {
    window.print();
  });

  // ========= INTERSECT UTILITIES =========
  function ensurePolygon(g) {
    if (!g) return null;
    if (g.type === "Polygon" || g.type === "MultiPolygon") return g;
    return null;
  }

  // ========= INTERSECT DETAIL =========
  function showIntersectDetail(feature, attrList) {
    const box = document.getElementById("detailBox");
    box.innerHTML = "";

    const h = document.createElement("h5");
    h.textContent = "HASIL INTERSECT";
    box.appendChild(h);

    const luasM2 = turf.area(feature);
    const luasHa = (luasM2 / 10000).toFixed(2);

    const row = document.createElement("div");
    row.className = "detail-row";
    row.innerHTML = `<div>Luas</div><div><strong>${luasHa} ha</strong></div>`;
    box.appendChild(row);

    const t = document.createElement("div");
    t.className = 'mt-3 mb-2';
    t.innerHTML = "<strong>Atribut yang digunakan:</strong>";
    box.appendChild(t);

    attrList.forEach(a => {
      const r = document.createElement("div");
      r.className = "detail-row";
      r.innerHTML = `<div>${a}</div><div>✔</div>`;
      box.appendChild(r);
    });
  }

  // ========= INTERSECT ANALYSIS =========
  function runIntersectAnalysis(attrList) {
    intersectLayerGroup.clearLayers();

    if (!geojsonData) return alert("GeoJSON belum dimuat.");

    let attrFeatures = attrList.map(attr => {
      return geojsonData.features.filter(f => f.properties.atribut === attr);
    });

    if (attrFeatures.some(f => f.length === 0)) {
      return alert("Ada atribut tanpa polygon pada data.");
    }

    let g1 = attrFeatures[0].map(f => ensurePolygon(f.geometry)).filter(Boolean);
    let g2 = attrFeatures[1].map(f => ensurePolygon(f.geometry)).filter(Boolean);

    let mid = [];

    g1.forEach(A => {
      g2.forEach(B => {
        let inter = turf.intersect(turf.feature(A), turf.feature(B));
        if (inter) mid.push(inter);
      });
    });

    let finalRes = mid;

    if (attrList.length === 3) {
      let g3 = attrFeatures[2].map(f => ensurePolygon(f.geometry)).filter(Boolean);
      let temp = [];

      mid.forEach(M => {
        g3.forEach(C => {
          let inter2 = turf.intersect(M, turf.feature(C));
          if (inter2) temp.push(inter2);
        });
      });

      finalRes = temp;
    }

    if (!finalRes || finalRes.length === 0) {
      return alert("Tidak ada area tumpang tindih untuk atribut terpilih.");
    }

    finalRes.forEach((ft, i) => {
      const color = `hsl(${(i * 71) % 360}, 75%, 45%)`;

      const LAYER = L.geoJSON(ft, {
        style: {
          color,
          weight: 3,
          fillOpacity: 0.55
        }
      }).addTo(intersectLayerGroup);

      LAYER.on("click", () => showIntersectDetail(ft, attrList));
    });

    alert("Intersect berhasil! Lihat area berwarna pada peta.");
  }

  // ========= INTERSECT BUTTONS =========
  document.getElementById("runIntersect").addEventListener("click", () => {
    const selected = Array.from(document.getElementById("intersectAttrs").selectedOptions)
      .map(o => o.value);

    if (selected.length < 2 || selected.length > 3) {
      return alert("Pilih 2 atau 3 atribut untuk intersect.");
    }

    runIntersectAnalysis(selected);
  });

  document.getElementById("clearIntersect").addEventListener("click", () => {
    intersectLayerGroup.clearLayers();
    document.getElementById("detailBox").innerHTML = "<em>Intersect dihapus.</em>";
  });

  // ========= INIT =========
  (async function init() {
    try {
      const [attrs, fc] = await Promise.all([fetchAttributes(), fetchGeoJSON()]);
      buildAtributChecks(attrs || []);
      prepareLayers(fc, attrs || []);
      geojsonData = fc;
      updateViewMode();
      try {
        const tempLayer = L.geoJSON(fc);
        const bounds = tempLayer.getBounds();
        if (bounds.isValid()) map.fitBounds(bounds, { padding: [20, 20], maxZoom: 13 });
      } catch (e) {
        console.warn("Zoom bounds invalid:", e);
      }
    } catch (e) {
      console.error(e);
      alert('Gagal memuat data peta. Periksa endpoint map.geojson / map.atribut di server.');
    }
  })();
</script>
@endsection
