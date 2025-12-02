@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold mb-4">Peta Kesesuaian Lahan</h1>

<div id="map" style="height:500px;"></div>

@section('scripts')
<script>
   var map = L.map('map').setView([-6.1, 106.15], 9);
   L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
</script>
@endsection

@endsection
