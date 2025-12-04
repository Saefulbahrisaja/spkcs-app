<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
</head>
<body style="margin:0">
<div id="map" style="width:100%; height:800px;"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([-6.3, 106.15], 9);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

fetch("{{ route('map.geojson') }}")
    .then(r=>r.json())
    .then(d=>{
        L.geoJSON(d).addTo(map);
    });
</script>
</body>
</html>
