<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>SPKCS - BANTEN SPABILITY</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('css/stylesadmin.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @yield('styles')
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
         <div class="d-flex align-items-center">
            <!-- LOGO -->
            <img src="{{ asset('storage/logo/banten.png') }}" 
                alt="Logo Banten" 
                style="height: 40px; width:auto;">

            <!-- TEKS BRAND -->
            <div class="ps-2 lh-sm">
                <a class="navbar-brand m-0 p-0 fw-bold" href="index.html" style="font-size: 18px;">
                    BANTEN-SPABILITY
                </a>
                <div style="font-size: 12px; margin-top: -4px; color: #777;">
                    (System of Paddy Land Suitability)
                </div>
            </div>
        </div>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group"></div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            @include('layouts.navbar')
        </ul>
    </nav>

    <!-- Sidebar (fixed under navbar) -->
    <div id="layoutSidenav">
        @include('layouts.sidebar')
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h2 class="mt-4"> <span>BANTEN-SPABILITY</span></h2>

                    {{-- Session messages moved inside main so they scroll with content --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Banten-SPABILITY (System of Paddy Land Suitability) 2025</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/demo/chart-bar-demo.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Semua dropdown input AHP
            document.querySelectorAll(".ahp-input").forEach(select => {
                select.addEventListener("change", function () {
                    let i = this.dataset.i;  // baris
                    let j = this.dataset.j;  // kolom
                    let val = this.value;
                    // Update hidden input submit value
                    document.getElementById(`input-${i}-${j}`).value = val;
                    // Hitung reciprocal
                    let rec = val ? (1 / parseFloat(val)).toFixed(4) : "";
                    // Cari elemen reciprocal lawan posisi (j,i)
                    let recInput = document.getElementById(`rec-${j}-${i}`);
                    if (recInput) {
                        recInput.value = rec;
                    }
                    // Update hidden DB value matrix[j][i]
                    let hiddenOpposite = document.getElementById(`input-${j}-${i}`);
                    if (hiddenOpposite) {
                        hiddenOpposite.value = rec;
                    }
                });
            });
        });
    </script>
    <script>
        document.querySelectorAll('.nilai-select').forEach(sel => {
            const colors = {
                1: '#dc3545',  // merah
                2: '#fd7e14',  // oranye
                3: '#ffc107',  // kuning
                4: '#0d6efd',  // biru
                5: '#198754'   // hijau
            };

            function applyColor(select) {
                let value = select.value;
                if (colors[value]) {
                    select.style.background = colors[value];
                    select.style.color = (value == 3 ? "black" : "white");
                    select.style.fontWeight = "bold";
                } else {
                    select.style.background = 'white';
                    select.style.color = 'black';
                }
            }

            // apply warna saat halaman dibuka
            applyColor(sel);

            // apply warna ketika user mengganti pilihan
            sel.addEventListener('change', function() {
                applyColor(this);
            });
        });
    </script>
</body>
</html>
