@extends('layouts.app')

@section('content')

<h1 class="text-xl font-bold mb-4">Ringkasan Luas Kesesuaian Lahan</h1>

<table class="table-auto w-full bg-white shadow">
    <thead>
        <tr class="bg-gray-200">
            <th>Kelas</th>
            <th>Wilayah</th>
            <th>Luas (ha)</th>
        </tr>
    </thead>

    <tbody id="ringkasan-body"></tbody>
</table>

@endsection


@section('scripts')
<script>
fetch("{{ route('ringkasan.luas') }}")
    .then(r => r.json())
    .then(data => {
        let tbody = document.getElementById("ringkasan-body");

        Object.keys(data).forEach(kelas => {
            Object.keys(data[kelas]).forEach(lokasi => {
                let luas = (data[kelas][lokasi] / 10000).toFixed(2); // m² → hektar

                tbody.innerHTML += `
                    <tr>
                        <td class="border p-2">${kelas}</td>
                        <td class="border p-2">${lokasi}</td>
                        <td class="border p-2">${luas} ha</td>
                    </tr>
                `;
            });
        });
    });
</script>
@endsection
