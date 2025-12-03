<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Banten - SPABILITY</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="asset/favicon.ico" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container px-5">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#!">
            <img src="{{ asset('storage/logo/banten.png') }}" alt="Logo Banten" height="40">
            <span>Banten Spability</span>
        </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#!">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Page Content-->
    <div class="container px-4 px-lg-5">
        <!-- Heading Row-->
        <div class="row gx-4 gx-lg-5 align-items-center my-5">
            <div class="col-lg-7">
                <div id="map" style="width: 100%; height: 400px; border-radius: 0.25rem;"></div>
            </div>
            <div class="col-lg-5">
                <h1 class="font-weight-light">Banten Spability</h1>
                <p>adalah sebuah platform cerdas berbasis webGIS yang dirancang untuk mengevaluasi kesesuaian lahan tanaman padi sawah secara spasial menggunakan data fisik lahan, topografi dan iklim, serta mekanisme pembobotan ilmiah yang dapat dikonfigurasi oleh pakar. Sistem ini menyediakan visualisasi peta kesesuaian (S1, S2, S3, N), ketersediaan lahan, laporan ringkas luas kesesuaian per wilayah, dan modul validasi pakar.</p>
                <p>Antarmuka yang user friendly dan alur analisis yang terstandar, Banten-SPABILITY mendukung pengambilan keputusan berbasis data untuk perencanaan tanaman padi sawah di Provinsi Banten.</p>
                <a class="btn btn-primary" href="#!">Selengkapnya</a>
            </div>
        </div>
        <!-- Call to Action-->
        <div class="card text-white bg-secondary my-5 py-4 text-center">
            <div class="card-body">
                <p class="text-white m-0">Rekomendasi dan Hasil Evaluasi Kesesuaian Lahan Padi Sawah</p>
            </div>
        </div>
        <!-- Content Row-->
        <div class="row gx-4 gx-lg-5 mb-4">
            <div class="col-12">
                <a href="{{ route('dashboard.export.pdf') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        <div class="row gx-4 gx-lg-5">
            <!-- =========== S1 =========== -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-success shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-success fw-bold">Sangat Sesuai (S1)</h3>
                        <p><strong>Jumlah Lokasi:</strong> {{ $S1->count() }}</p>
                        <strong>Daftar Wilayah:</strong>
                        @if($S1->count())
                            <ul>
                                @foreach($S1 as $w)
                                    <li>{{ $w->lokasi }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Tidak ada wilayah S1</span>
                        @endif
                        <strong>Total Luas:</strong> {{ number_format($totalS1, 2) }} ha
                    </div>
                </div>
            </div>
            <!-- =========== S2 =========== -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-warning shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-warning fw-bold">Cukup Sesuai (S2)</h3>
                        <p><strong>Jumlah Lokasi:</strong> {{ $S2->count() }}</p>
                        <strong>Daftar Wilayah:</strong>
                        @if($S2->count())
                            <ul>
                                @foreach($S2 as $w)
                                    <li>{{ $w->lokasi }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Tidak ada wilayah S2</span>
                        @endif
                        <strong>Total Luas:</strong> {{ number_format($totalS2, 2) }} ha
                    </div>
                </div>
            </div>
            <!-- =========== S3 =========== -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 shadow-sm" style="border-color:#ff8800;">
                    <div class="card-body">
                        <h3 class="card-title fw-bold" style="color:#ff8800;">Marginal (S3)</h3>
                        <p><strong>Jumlah Lokasi:</strong> {{ $S3->count() }}</p>
                        <strong>Daftar Wilayah:</strong>
                        @if($S3->count())
                            <ul>
                                @foreach($S3 as $w)
                                    <li>{{ $w->lokasi }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Tidak ada wilayah S3</span>
                        @endif
                        <strong>Total Luas:</strong> {{ number_format($totalS3, 2) }} ha
                    </div>
                </div>
            </div>
            <!-- =========== N =========== -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 shadow-sm" style="border-color:#ff0900;">
                    <div class="card-body">
                        <h3 class="card-title fw-bold" style="color:#ff0900;">Tidak Sesuai (N)</h3>
                        <p><strong>Jumlah Lokasi:</strong> {{ $N->count() }}</p>
                        <strong>Daftar Wilayah:</strong>
                        @if($N->count())
                            <ul>
                                @foreach($N as $w)
                                    <li>{{ $w->lokasi }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Tidak ada wilayah N</span>
                        @endif
                        <strong>Total Luas:</strong> {{ number_format($totalN, 2) }} ha
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer-->
    <footer class="py-5 bg-dark">
        <div class="container px-4 px-lg-5">
            <p class="m-0 text-center text-white">Copyright &copy; Banten-SPABILITY (System of Paddy Land Suitability) 2023</p>
        </div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // ================== INISIALISASI MAP =====================
        var map = L.map('map', {
            center: [-6.3, 106.15],
            zoom: 9
        });

        // Basemap OSM
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // ================== WARNA KELAS =====================
        function warnaKelas(k) {
            return {
                "S1": "#00aa00",
                "S2": "#d4d40d",
                "S3": "#ff8800",
                "N": "#cc0000"
            }[k] || "#666";
        }

        // ================== LOAD GEOJSON SAJA =====================
        fetch("{{ route('map.geojson') }}")
            .then(res => res.json())
            .then(data => {
                L.geoJSON(data, {
                    style: f => ({
                        color: warnaKelas(f.properties.kelas_kesesuaian),
                        weight: 2,
                        fillOpacity: 0.5
                    }),
                    onEachFeature: (f, layer) => {
                        let p = f.properties;
                        layer.bindPopup(`
                            <b>Lokasi:</b> ${p.lokasi} <br>
                            <b>Kelas:</b> ${p.kelas_kesesuaian ?? "-"}
                        `);
                    }
                }).addTo(map);
            })
            .catch(err => console.error("GeoJSON error:", err));
    </script>
</body>
</html>
