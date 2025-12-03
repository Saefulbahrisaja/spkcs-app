<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Small Business - Start Bootstrap Template</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="asset/favicon.ico" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    </head>
    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container px-5">
                <a class="navbar-brand" href="#!">Banten Spability</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="#!">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link" href="#!">Tentang</a></li>
                        <li class="nav-item"><a class="nav-link" href="#!">Peta Administrasi</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
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
                    <p>adalah sebuah platform cerdas berbasis webGIS yang dirancang untuk mengevaluasi kesesuaian lahan tanaman
padi sawah secara spasial menggunakan data fisik lahan, topografi
dan iklim, serta mekanisme pembobotan ilmiah yang dapat
dikonfigurasi oleh pakar. Sistem ini menyediakan visualisasi peta
kesesuaian (S1, S2, S3, N), ketersediaan lahan, laporan ringkas luas
kesesuaian per wilayah, dan modul validasi pakar. </p>
                    <p>Antarmuka yang
user friendly dan alur analisis yang terstandar, Banten-SPABILITY
mendukung pengambilan keputusan berbasis data untuk
perencanaan tanaman padi sawah di Provinsi Banten.</p>
                    <a class="btn btn-primary" href="#!">Selengkapnya</a>
                   
                </div>
            </div>
            <!-- Call to Action-->
            <div class="card text-white bg-secondary my-5 py-4 text-center">
                <div class="card-body"><p class="text-white m-0">This call to action card is a great place to showcase some important information or display a clever tagline!</p></div>
            </div>
            <!-- Content Row-->
            <div class="row gx-4 gx-lg-5">
                <div class="col-md-4 mb-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="card-title">Card One</h2>
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Rem magni quas ex numquam, maxime minus quam molestias corporis quod, ea minima accusamus.</p>
                        </div>
                        <div class="card-footer"><a class="btn btn-primary btn-sm" href="#!">More Info</a></div>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="card-title">Card Two</h2>
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quod tenetur ex natus at dolorem enim! Nesciunt pariatur voluptatem sunt quam eaque, vel, non in id dolore voluptates quos eligendi labore.</p>
                        </div>
                        <div class="card-footer"><a class="btn btn-primary btn-sm" href="#!">More Info</a></div>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="card-title">Card Three</h2>
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Rem magni quas ex numquam, maxime minus quam molestias corporis quod, ea minima accusamus.</p>
                        </div>
                        <div class="card-footer"><a class="btn btn-primary btn-sm" href="#!">More Info</a></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container px-4 px-lg-5"><p class="m-0 text-center text-white">Copyright &copy; Your Website 2023</p></div>
        </footer>
        <!-- Bootstrap core JS-->
         <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

         <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
     
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
            "N":  "#cc0000"
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
