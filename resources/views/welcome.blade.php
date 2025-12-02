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
                    <p>Banten SPABILITY adalah platform digital yang menyediakan informasi komprehensif tentang potensi pertanian dan sumber daya alam di Provinsi Banten. Kami berkomitmen untuk memberdayakan petani lokal melalui teknologi dan data yang akurat.</p>
                    <p>Tanaman Padi Sawah merupakan komoditas unggulan Banten yang memiliki produktivitas tinggi. Sistem pertanian sawah tradisional kami menghasilkan padi berkualitas premium dengan hasil panen yang optimal sepanjang tahun.</p>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        @section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// ================== BASemaps =====================
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

// ================== Inisialisasi Map =====================
var map = L.map('map', {
    center: [-6.2, 106.15],
    zoom: 11,
    layers: [osm]   // layer default
});

// ================== Warna Kelas =====================
function warnaKelas(k) {
    return {
        'S1': '#00aa00',
        'S2': '#d4d40d',
        'S3': '#ff8800',
        'N':  '#cc0000'
    }[k] || '#999';
}

// ================== Overlay Kesesuaian Lahan =====================
var layerKesesuaian = L.layerGroup();

// Load GeoJSON dari Controller
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
    });

// ================== Layer Control =====================
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
    </body>
</html>
