@extends('layouts.app')

@section('content')
@php
$existingJson = $existingWilayah->map(function($w){
    return [
        'lokasi' => $w['lokasi'],
        'lat' => $w['lat'],
        'lng' => $w['lng'],
        'geojson' => $w['geojson'], // feature
        'nilai_dinamis' => $w['nilai_dinamis']
    ];
})->toArray();
@endphp


<div class="container">

    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.wilayah.index') }}">Daftar Lokasi</a></li>
        <li class="breadcrumb-item active">Tambah Wilayah</li>
    </ol>

    <!-- Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <strong><i class="fas fa-map"></i> Tambah Wilayah – Wizard per Polygon</strong>
        </div>

        <div class="card-body">

            <!-- Form -->
            <form id="uploadForm" action="{{ route('admin.wilayah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama Lokasi -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lokasi (Opsional)</label>
                    <input type="text" name="lokasi" class="form-control shadow-sm" placeholder="Contoh: Kecamatan Sukamaju">
                </div>

                <!-- Upload File -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Upload GeoJSON / ZIP Shapefile</label>
                    <input id="geoFile" type="file" name="geo" class="form-control shadow-sm" accept=".zip,.geojson,.json" required>
                    <small class="text-muted">Format yang didukung: .zip (SHP) atau .geojson</small>
                </div>

                <!-- Map -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Peta Wilayah</label>
                    <div id="mapPreview" class="rounded shadow-sm" style="height:480px;border:1px solid #ccc;"></div>
                </div>

                <!-- ⚠ PREVIEW ATRIBUT DIHAPUS
                <div class="mb-3">
                    <label class="form-label fw-semibold">Daftar Atribut (Preview)</label>
                    <div id="featureList" class="rounded shadow-sm" style="...">
                        <em>Belum ada file.</em>
                    </div>
                </div>
                -->

                <input type="hidden" id="nilai_storage" name="nilai_storage">

                <button id="submitBtn" class="btn btn-success px-4 shadow-sm" disabled>
                    <i class="fas fa-save"></i> Simpan Semua Wilayah
                </button>

                <a href="{{ route('admin.wilayah.index') }}" class="btn btn-secondary shadow-sm px-4 ms-2">Batal</a>
            </form>
        </div>
    </div>

</div>

<!-- WIZARD -->
<div class="modal fade" id="wizardModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg border-0">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-edit"></i> Input Data Polygon</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4" id="wizardBody"></div>

      <div class="modal-footer">
        <button id="wizardPrev" class="btn btn-secondary px-4">Sebelumnya</button>
        <button id="wizardAddAttr" class="btn btn-outline-primary px-4">+ Tambah Atribut</button>
        <button id="wizardNext" class="btn btn-primary px-4">Selanjutnya</button>
      </div>

    </div>
  </div>
</div>

<!-- ASSET -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
<script src="https://unpkg.com/shpjs/dist/shp.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const existingDB = @json($existingJson);

    let nilaiWilayah = {};
    let modalSteps = [];
    let modalCurrentStep = 0;
    let editingIndex = null;
    let currentGeoJson = null;
    let geoLayer = null;
    let layerList = [];

    const COLOR_EMPTY = "#d9534f";
    const COLOR_FILLED = "#28a745";

    const map = L.map('mapPreview').setView([-6.2,106.8], 8);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);

    const fileInput = document.getElementById('geoFile');
    const submitBtn = document.getElementById('submitBtn');
    const nilaiStorageEl = document.getElementById('nilai_storage');

    const wizardModal = new bootstrap.Modal(document.getElementById('wizardModal'));
    const wizardBody = document.getElementById('wizardBody');
    const btnPrev = document.getElementById('wizardPrev');
    const btnNext = document.getElementById('wizardNext');
    const btnAddAttr = document.getElementById('wizardAddAttr');

    function geometryEqual(g1, g2){
        return JSON.stringify(g1) === JSON.stringify(g2);
    }

    /* =======================
        FILE HANDLER
    ======================= */
    fileInput.addEventListener('change', async evt => {
        const file = evt.target.files[0];
        if (!file) return;

        try {
            let gj = file.name.endsWith('.zip')
                ? await readShp(file)
                : JSON.parse(await file.text());

            currentGeoJson = gj;
            nilaiWilayah = {};

            drawPolygons(gj, () => restoreExistingData(gj));

            submitBtn.disabled = false;

        } catch (err) {
            alert("Gagal membaca file: " + err.message);
        }
    });

    async function readShp(file){
        const zip = await JSZip.loadAsync(file);
        let shpFile, shxFile, dbfFile;

        zip.forEach((rel, entry)=>{
            const p = rel.toLowerCase();
            if (p.endsWith('.shp')) shpFile = entry;
            if (p.endsWith('.shx')) shxFile = entry;
            if (p.endsWith('.dbf')) dbfFile = entry;
        });

        return await shp({
            shp: await shpFile.async("arraybuffer"),
            shx: await shxFile.async("arraybuffer"),
            dbf: await dbfFile.async("arraybuffer")
        });
    }

    /* =======================
        DRAW POLYGONS
    ======================= */
    function drawPolygons(gj, callback=null){
        if (geoLayer){ map.removeLayer(geoLayer); layerList = []; }

        geoLayer = L.geoJSON(gj, {
            style:()=>({ color:COLOR_EMPTY, weight:2, fillOpacity:.35 }),
            onEachFeature:(feat, layer)=>{
                const idx = gj.features.indexOf(feat);
                layerList[idx] = layer;

                layer.on('click', ()=> openWizard(idx));
            }
        }).addTo(map);

        try { map.fitBounds(geoLayer.getBounds()); } catch {}

        if (callback) setTimeout(callback, 120);
    }

    /* =======================
        RESTORE DATA EXISTING
    ======================= */
    function restoreExistingData(gj){
        gj.features.forEach((feat, idx)=>{
            const match = existingDB.find(e =>
                e.geojson &&
                e.geojson.geometry &&
                geometryEqual(e.geojson.geometry, feat.geometry)
            );

            if (match){
                nilaiWilayah[idx] = {
                    nama: match.lokasi,
                    atribut: match.nilai_dinamis.map(a => ({
                        nama: a.atribut_nama,
                        nilai: a.nilai
                    }))
                };

                layerList[idx].setStyle({ color:COLOR_FILLED, fillOpacity:.65 });
            }
        });

        nilaiStorageEl.value = JSON.stringify(nilaiWilayah);
    }

    /* =======================
        WIZARD
    ======================= */
    function openWizard(idx){
        editingIndex = idx;

        if (!nilaiWilayah[idx])
            nilaiWilayah[idx] = { nama:"", atribut:[{nama:"", nilai:""}] };

        buildWizard(idx);

        renderWizardStep();
        wizardModal.show();
    }

    function buildWizard(idx){
        modalSteps = [{ type:'name' }];
        nilaiWilayah[idx].atribut.forEach((a,i)=>{
            modalSteps.push({ type:'attr', index:i });
        });
        modalCurrentStep = 0;
    }

    function renderWizardStep(){
        const step = modalSteps[modalCurrentStep];

        btnPrev.style.display = modalCurrentStep === 0 ? "none" : "inline-block";
        btnNext.textContent = modalCurrentStep === modalSteps.length-1 ? "Selesai" : "Selanjutnya";

        if (step.type === 'name'){
            wizardBody.innerHTML = `
                <label>Nama Wilayah</label>
                <input id="wiz_name" class="form-control"
                       value="${nilaiWilayah[editingIndex].nama}">
            `;
        }
        else {
            const a = nilaiWilayah[editingIndex].atribut[step.index];
            wizardBody.innerHTML = `
                <label>Nama Atribut</label>
                <input id="wiz_attr_name" class="form-control" value="${a.nama}">

                <label class="mt-3">Nilai</label>
                <input id="wiz_attr_value" class="form-control" value="${a.nilai}">

                <button id="removeAttr" class="btn btn-danger btn-sm mt-3">Hapus</button>
            `;

            setTimeout(()=>{
                document.getElementById("removeAttr").onclick = () => removeAttr(step.index);
            },10);
        }
    }

    function saveWizard(){
        const step = modalSteps[modalCurrentStep];

        if (step.type === 'name'){
            nilaiWilayah[editingIndex].nama =
                document.getElementById('wiz_name').value.trim();
        } else {
            const idx = step.index;
            nilaiWilayah[editingIndex].atribut[idx] = {
                nama: document.getElementById('wiz_attr_name').value.trim(),
                nilai: document.getElementById('wiz_attr_value').value.trim()
            };
        }

        nilaiStorageEl.value = JSON.stringify(nilaiWilayah);
    }

    btnAddAttr.onclick = ()=>{
        saveWizard();
        nilaiWilayah[editingIndex].atribut.push({ nama:"", nilai:"" });
        buildWizard(editingIndex);
        modalCurrentStep = modalSteps.length-1;
        renderWizardStep();
    };

    function removeAttr(i){
        nilaiWilayah[editingIndex].atribut.splice(i,1);
        if (nilaiWilayah[editingIndex].atribut.length === 0)
            nilaiWilayah[editingIndex].atribut.push({nama:"", nilai:""});

        buildWizard(editingIndex);
        modalCurrentStep = Math.min(modalCurrentStep, modalSteps.length-1);
        renderWizardStep();
    }

    btnNext.onclick = ()=>{
        saveWizard();
        if (modalCurrentStep < modalSteps.length-1){
            modalCurrentStep++;
            renderWizardStep();
        } else {
            wizardModal.hide();
            layerList[editingIndex].setStyle({color:COLOR_FILLED, fillOpacity:.65});
        }
    };

    btnPrev.onclick = ()=>{
        saveWizard();
        modalCurrentStep--;
        renderWizardStep();
    };

});
</script>

@endsection
