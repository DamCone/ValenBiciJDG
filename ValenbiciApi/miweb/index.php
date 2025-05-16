<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disponibilidad de ValenBisi</title>
    <link rel="stylesheet" type="text/css" href="estilos.css">
</head>
<body>
    <h1>🚲 Disponibilidad de ValenBisi</h1>
    
    <div id="contenedorBusqueda"><input type="text" id="busqueda" placeholder="Buscar por dirección..." onkeyup="filtrarTabla()"></div>
    
    <form method="get" style="text-align: center; margin: 10px;">
        <label for="limite" id="txtnum">Número de estaciones a mostrar:</label>
        <input type="number" name="limite" id="limite" min="1" max="200" value="<?= isset($_GET['limite']) ? (int)$_GET['limite'] : 20 ?>">
        <input type="submit" value="Actualizar">
    </form>
    
    <?php
    $baseUrl = "https://valencia.opendatasoft.com/api/explore/v2.1/catalog/datasets/valenbisi-disponibilitat-valenbisi-dsiponibilidad/records?";
    $limit = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
    $offset = 0;
    $allStations = [];
    $errorOccurred = false;

    do {
        $url = $baseUrl . "limit=" . $limit . "&offset=" . $offset;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para desarrollo
        $response = curl_exec($ch);

        if ($response === false) {
            echo "<p class='error'>Error en cURL: " . curl_error($ch) . "</p>";
            $errorOccurred = true;
            break;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            echo "<p class='error'>Error en la solicitud a la API (HTTP $httpCode).</p>";
            $errorOccurred = true;
            break;
        }

        curl_close($ch);
        $data = json_decode($response, true);

        if ($data === null) {
            echo "<p class='error'>Error al decodificar la respuesta JSON.</p>";
            $errorOccurred = true;
            break;
        }

        if (isset($data["results"]) && is_array($data["results"]) && count($data["results"]) > 0) {
            foreach ($data["results"] as $station) {
                $allStations[$station['number']] = [
                    'address' => $station['address'],
                    'open' => ($station['open'] == "T"),
                    'available' => (int) $station['available'],
                    'free' => (int) $station['free'],
                    'total' => (int) $station['total'],
                    'updated_at' => $station['updated_at'],
                    'lat' => $station['geo_point_2d']['lat'],
                    'lon' => $station['geo_point_2d']['lon']
                ];
            }
            $offset += $limit;
        } else {
            echo "<p class='warning'>No hay resultados o el formato es incorrecto.</p>";
            break;
        }

    } while (false);

    if (!$errorOccurred && !empty($allStations)) {
        $filePath = getcwd() . '/data.json';
        file_put_contents($filePath, json_encode($allStations));

        $ultimaActualizacion = end($allStations)['updated_at'];
        echo "<p class='info'>Última actualización: $ultimaActualizacion</p>";

        echo "<table>";
        echo "<tr><th>Dirección</th><th>ID</th><th>Abierto</th><th>Disponibles</th><th>Libres</th><th>Total</th><th>Actualizado</th><th>Coordenadas</th></tr>";

        foreach ($allStations as $number => $station) {
            $color = "";
            if ($station['available'] == 0) {
                $color = "background-color: #f8d7da;"; // rojo claro
            } elseif ($station['available'] < 5) {
                $color = "background-color: #fff3cd;"; // amarillo claro
            } else {
                $color = "background-color: #d4edda;"; // verde claro
            }

            echo "<tr style='$color'>";
            echo "<td>" . htmlspecialchars($station['address']) . "</td>";
            echo "<td>" . $number . "</td>";
            echo "<td>" . ($station['open'] ? "Sí" : "No") . "</td>";
            echo "<td>" . $station['available'] . "</td>";
            echo "<td>" . $station['free'] . "</td>";
            echo "<td>" . $station['total'] . "</td>";
            echo "<td>" . $station['updated_at'] . "</td>";
            echo "<td>Lon(" . $station['lon'] . "), Lat(" . $station['lat'] . ")</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p class='warning'>No se encontraron datos de estaciones.</p>";
    }
    ?>

    <div id="btn">
        <a href="mapearbicis.php">Ver mapa de estaciones</a>
    </div>

    <script>
        function filtrarTabla() {
            var input = document.getElementById("busqueda");
            var filter = input.value.toUpperCase();
            var table = document.querySelector("table");
            var tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().includes(filter) ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>
