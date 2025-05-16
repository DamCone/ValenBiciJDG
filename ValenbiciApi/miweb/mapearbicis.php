<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Mapeo de Bicicletas en Valencia</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <style>
            body {
                background-color: whitesmoke;
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }

            h2 {
                text-align: center;
                margin: 10px;
                color: green;
            }

            #map {
                height: 90vh;
                width: 100%;
            }

            .legend {
                position: absolute;
                bottom: 20px;
                left: 10px;
                background: white;
                padding: 10px;
                font-size: 14px;
                border-radius: 5px;
                box-shadow: 0 0 5px rgba(0,0,0,0.3);
            }

            .legend div {
                margin-bottom: 5px;
            }

            .legend span {
                display: inline-block;
                width: 12px;
                height: 12px;
                margin-right: 5px;
            }

            .buscador {
                text-align: center;
                margin: 10px;
            }

            .buscador input {
                padding: 8px;
                width: 300px;
                border-radius: 5px;
                border: 1px solid #ccc;
            }
        </style>
    </head>
    <body>

        <h2>Mapeo de Bicicletas en Valencia</h2>

        <div class="buscador">
            <input type="text" id="searchInput" placeholder="Buscar dirección de estación..." onkeyup="filtrarEstaciones()">
        </div>

        <div id="map"></div>

        <div class="legend" id="leyenda">
            <strong>Leyenda:</strong>
            <div><span style="background: red;"></span> Baja disponibilidad (0-2 bicis)</div>
            <div><span style="background: orange;"></span> Media disponibilidad (3-5 bicis)</div>
            <div><span style="background: green;"></span> Alta disponibilidad (6+ bicis)</div>
        </div>

        <script>
            var mapa = L.map('map').setView([39.4699, -0.3763], 13); // Coordenadas de Valencia

            // Capa base de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapa);

            let marcadores = [];

            // Cargar datos del archivo JSON generado por el otro PHP
            fetch('data.json')
                    .then(response => response.json())
                    .then(data => {
                        for (const id in data) {
                            const estacion = data[id];
                            const disponibles = estacion.available;
                            const direccion = estacion.address;

                            // Color del marcador
                            let color;
                            if (disponibles <= 2) {
                                color = "red";
                            } else if (disponibles <= 5) {
                                color = "orange";
                            } else {
                                color = "green";
                            }

                            const marker = L.circleMarker([estacion.lat, estacion.lon], {
                                radius: 10,
                                fillColor: color,
                                color: "#000",
                                weight: 1,
                                fillOpacity: 0.8
                            });

                            marker.on('mouseover', function (e) {
                                this.setStyle({
                                    weight: 3,
                                    radius: 12
                                });
                            });

                            marker.on('mouseout', function (e) {
                                this.setStyle({
                                    weight: 1,
                                    radius: 10,

                                });
                            });

                            marker.bindPopup(`
                            <strong>${direccion}</strong><br>
                            Disponibles: ${disponibles}<br>
                            Libres: ${estacion.free}<br>
                            Total: ${estacion.total}<br>
                            Estado: ${estacion.open ? 'Abierto' : 'Cerrado'}
                        `);

                            marker.addTo(mapa);
                            marcadores.push({marker, direccion});
                        }
                    });

            function filtrarEstaciones() {
                const input = document.getElementById("searchInput").value.toLowerCase();
                for (const obj of marcadores) {
                    const visible = obj.direccion.toLowerCase().includes(input);
                    if (visible) {
                        mapa.addLayer(obj.marker);
                    } else {
                        mapa.removeLayer(obj.marker);
                    }
                }
            }
        </script>

    </body>
</html>
