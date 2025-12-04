@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Ringkasan Luas Kesesuaian</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Chart Ringkasan Luas Kesesuaian Lahan</h5>
    </div>

    <div class="card-body">

        {{-- Loading Spinner --}}
        <div id="loadingSpinner" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Memuat data chart...</p>
        </div>

        {{-- Charts --}}
        <div id="chartsContainer" class="row d-none">

            {{-- Pie Chart --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-center mb-3">Pie Chart Kelas Kesesuaian</h6>
                        <canvas id="pieChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            {{-- Bar Chart --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-center mb-3">Bar Chart Luas per Wilayah</h6>
                        <canvas id="barChart" height="180"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
fetch("{{ route('admin.ringkasan.luas') }}")
    .then(r => r.json())
    .then(data => {

        // ========== PIE CHART ==========
        let pieLabels = ["S1", "S2", "S3", "N"];
        let pieValues = [];

        pieLabels.forEach(kelas => {
            let total = Object.values(data[kelas] ?? {}).reduce((a,b) => a + b, 0);
            pieValues.push((total / 10000).toFixed(2)); // m2 → ha
        });

        new Chart(document.getElementById("pieChart"), {
            type: "pie",
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieValues,
                    backgroundColor: ["#22c55e", "#eab308", "#f97316", "#ef4444"]
                }]
            }
        });

        // ========== BAR CHART ==========
        let wilayahSet = new Set();

        Object.keys(data).forEach(k => {
            Object.keys(data[k]).forEach(w => wilayahSet.add(w));
        });

        let wilayah = Array.from(wilayahSet);

        let barDatasets = ["S1","S2","S3","N"].map(kelas => ({
            label: kelas,
            data: wilayah.map(w => ((data[kelas][w] ?? 0) / 10000).toFixed(2)),
            backgroundColor: {
                S1: "#22c55e",
                S2: "#eab308",
                S3: "#f97316",
                N:  "#ef4444",
            }[kelas]
        }));

        new Chart(document.getElementById("barChart"), {
            type: "bar",
            data: {
                labels: wilayah,
                datasets: barDatasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "top" }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: "Luas (ha)" }
                    }
                }
            }
        });

        // Tampilkan chart setelah loading selesai
        document.getElementById("loadingSpinner").classList.add("d-none");
        document.getElementById("chartsContainer").classList.remove("d-none");

    });
</script>
@endsection
