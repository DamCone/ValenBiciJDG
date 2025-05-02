<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Disponibilidad de ValenBisi</title>
 
<link rel="stylesheet" type="text/css" href="estilos.css">

</head>
<body>
<h1>Disponibilidad de ValenBisi</h1>
<?php
$baseUrl = "https://valencia.opendatasoft.com/api/explore/v2.1/catalog/datasets/valenbisi-disponibilitat-valenbisi-dsiponibilidad/records?";
$limit = 5;
$offset = 0;
$allStations = [];
$errorOccurred = false;
do {
 $url = $baseUrl . "limit=" . $limit . "&offset=" . $offset;
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //Desactivar la verificación del certificado SSL. (Solo para desarrollo)
 $response = curl_exec($ch);
 if ($response === false) {
 echo "<p style='color: red; text-align: center;'>Error en cURL: " . curl_error($ch) . "</p>";
 $errorOccurred = true;
 break;
 }
 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 if ($httpCode != 200) {
 echo "<p style='color: red; text-align: center;'>Error en la solicitud a la API (Código HTTP: " . $httpCode . ").
URL: " . $url . "</p>";
 $errorOccurred = true;
 break;
 }
 curl_close($ch);
 $data = json_decode($response, true);
 if ($data === null) {
 echo "<p style='color: red; text-align: center;'>Error al decodificar la respuesta JSON. Response: " .
htmlspecialchars($response) . "</p>"; // Escapa caracteres especiales para seguridad
 $errorOccurred = true;
 break;
 }
 if (isset($data["results"]) && is_array($data["results"]) && count($data["results"]) > 0) {
 foreach ($data["results"] as $station) {
 $allStations[$station['number']] = [
 'address' => $station['address'],
 'open' => ($station['open'] == "T"),
 'available' => (int)$station['available'],
 'free' => (int)$station['free'],
 'total' => (int)$station['total'],
 'updated_at' => $station['updated_at'],
 'lat' => $station['geo_point_2d']['lat'],
 'lon' => $station['geo_point_2d']['lon']
         ];
 }
 $offset += $limit;
 } else {
 echo "<p style='color: orange; text-align: center;'>No hay resultados en esta página o el formato de la
respuesta es incorrecto.</p>";
 var_dump($data); // Imprime $data para depuración
 break;
 }
} while (false);
if (!$errorOccurred && !empty($allStations)) { // Usamos !empty() para verificar si $allStations tiene elementos
 $filePath = getcwd() . '/data.json';
 if(file_put_contents($filePath, json_encode($allStations))){
 echo "<p style='color: green; text-align: center;'>Datos guardados en: " . $filePath . "</p>";
 } else {
 echo "<p style='color: red; text-align: center;'>Error al guardar el archivo data.json. Verifica los permisos de
escritura.</p>";
 }
} elseif (!$errorOccurred && empty($allStations)) {
 echo "<p style='color: orange; text-align: center;'>No se encontraron datos de estaciones.</p>";
}
if (!empty($allStations)) {
 echo "<table>";
 echo
"<tr><th>Dirección</th><th>Número</th><th>Abierto</th><th>Disponibles</th><th>Libres</th><th>Total
</th><th>Actualizado</th><th>Coordenadas</th></tr>";
 foreach ($allStations as $number => $station) {
 echo "<tr>";
 echo "<td><strong>Dirección:</strong> " . htmlspecialchars($station['address']) . "</td>"; // Escapa caracteres especiales
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
}
?>
<!--Botón-->
<div id="btn">
    <a href="mapearbicis.php">
        Ver mapa de estaciones
    </a>
</div>

</body>
</html>
