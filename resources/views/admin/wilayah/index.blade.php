@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold mb-4">Daftar Lokasi</h1>

<a href="{{ route('admin.wilayah.create') }}" 
    class="bg-blue-600 text-white px-3 py-2 rounded">Tambah Lokasi</a>
<a href="{{ route('admin.alternatif.index') }}" 
    class="bg-blue-600 text-white px-3 py-2 rounded ml-2">Input Nilai Alternatif</a>

<div class="mt-6">
  <div class="flex border-b">
     <button class="tab-button px-4 py-2 font-semibold border-b-2 border-blue-600 text-blue-600" onclick="showTab(0)">Daftar Lokasi</button>
     <button class="tab-button px-4 py-2 font-semibold text-gray-600" onclick="showTab(1)">Daftar Nilai Alternatif</button>
  </div>

  <!-- Tab 1: Daftar Lokasi -->
  <div id="tab-0" class="tab-content">
     <table class="table-auto w-full mt-4 bg-white shadow">
        <thead>
          <tr class="bg-gray-200">
             <th class="p-2">Lokasi</th>
             <th class="p-2">Lat/Long</th>
             <th class="p-2">Nilai</th>
             <th class="p-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $item)
             <tr class="border-b">
                <td class="p-2">{{ $item->lokasi }}</td>
                <td class="p-2">
                  <a href="https://maps.google.com/?q={{ $item->lat }},{{ $item->lng }}" target="_blank" class="text-blue-600 hover:underline">
                     {{ $item->lat }}, {{ $item->lng }}
                  </a>
                </td>
                <td class="p-2">{{ $item->nilai_total }}</td>
                <td class="p-2">
                  <form action="{{ route('admin.wilayah.destroy', $item->id) }}" method="POST" class="inline">
                     @csrf
                     @method('DELETE')
                     <button type="submit" onclick="return confirm('Hapus?')" class="bg-red-600 text-white px-2 py-1 rounded">Hapus</button>
                  </form>
                </td>
             </tr>
          @endforeach
        </tbody>
     </table>
  </div>

  <!-- Tab 2: Daftar Nilai Alternatif -->
  <div id="tab-1" class="tab-content hidden">
     <div class="bg-white shadow rounded p-4 mt-4">
        <table class="w-full table-auto border text-sm">
          <thead>
             <tr class="bg-gray-200 text-left">
                <th class="p-2 border">Alternatif</th>
                @foreach($kriteria as $k)
                  <th class="p-2 border">{{ $k->nama_kriteria }}</th>
                @endforeach
             </tr>
          </thead>
          <tbody>
             @foreach($data as $alt)
                <tr>
                  <td class="p-2 border font-semibold">{{ $alt->lokasi }}</td>
                  @foreach($kriteria as $k)
                     @php
                        $nilai = $alt->nilai->where('kriteria_id', $k->id)->first();
                     @endphp
                     <td class="p-2 border text-center">
                        {{ $nilai->nilai ?? '-' }}
                     </td>
                  @endforeach
                </tr>
             @endforeach
          </tbody>
        </table>
     </div>
  </div>
</div>

<script>
  function showTab(tabIndex) {
     // Hide all tabs
     document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
     });
     
     // Remove active state from all buttons
     document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
        btn.classList.add('text-gray-600');
     });
     
     // Show selected tab
     document.getElementById('tab-' + tabIndex).classList.remove('hidden');
     
     // Add active state to clicked button
     event.target.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
     event.target.classList.remove('text-gray-600');
  }
</script>

@endsection
