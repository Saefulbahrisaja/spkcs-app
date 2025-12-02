@extends('layouts.app')

@section('content')

<h1 class="text-xl font-bold mb-4">Chart Ringkasan Luas Kesesuaian Lahan</h1>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="flex justify-center items-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"> Memuat Data </div>
</div>

<div id="chartsContainer" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Pie Chart -->
    <div class="bg-white p-4 shadow rounded">
        <h2 class="font-semibold mb-2 text-center">Pie Chart Kelas Kesesuaian</h2>
        <canvas id="pieChart"></canvas>
    </div>

    <!-- Bar Chart -->
    <div class="bg-white p-4 shadow rounded">
        <h2 class="font-semibold mb-2 text-center">Bar Chart Per Wilayah</h2>
        <canvas id="barChart"></canvas>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
fetch("{{ route('admin.ringkasan.luas') }}")
    .then(r => r.json())
    .then(data => {

        // ========= PIE CHART =========
        let pieLabels = ["S1", "S2", "S3", "N"];
        let pieValues = [];

        pieLabels.forEach(kelas => {
            let total = Object.values(data[kelas] ?? {})
                .reduce((a,b) => a + b, 0);

            pieValues.push((total / 10000).toFixed(2)); // ha
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

        // ========= BAR CHART =========
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
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: "Luas (ha)" }
                    }
                }
            }
        });

        // Hide loading & show charts
        document.getElementById("loadingSpinner").classList.add("hidden");
        document.getElementById("chartsContainer").classList.remove("hidden");

    });
</script>
@endsection
