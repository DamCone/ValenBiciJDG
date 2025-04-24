
package com.mycompany.valenbiciapiv2;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.IOException;

public class DatosJSon {
 private static String API_URL;
 private String datos = ""; //para mostrar en el jTextArea los datos de las estaciones

 private String [] values; //para añadir los datos de las estaciones Valenbici a la BDD
 private int numEst;

 public DatosJSon(int nE){
 numEst = nE;
 datos = "";
 API_URL = "https://valencia.opendatasoft.com/api/explore/v2.1/catalog/datasets/valenbisi-disponibilitat-valenbisi-dsiponibilidad/records?f=json&location=39.46447,-0.39308&distance=10&limit=" + nE;

 values = new String [numEst];

 for (int i = 0; i < numEst; i++ )
 values[i] = "";
 }


 public void mostrarDatos(int nE){

     numEst = nE;
     datos = ""; // Reinicia 'datos' cada vez que se llama
     API_URL = "https://valencia.opendatasoft.com/api/explore/v2.1/catalog/datasets/valenbisi-disponibilitat-valenbisi-dsiponibilidad/records?f=json&location=39.46447,-0.39308&distance=10&limit=" + nE;

     values = new String [numEst];
     for (int i = 0; i < numEst; i++ )
         values[i] = "";

     if (API_URL.isEmpty()) {
         setDatos(getDatos().concat("La URL de la API no está especificada."));
     }

     StringBuilder datosBuilder = new StringBuilder();

     try (CloseableHttpClient httpClient = HttpClients.createDefault()) {
         HttpGet request = new HttpGet(API_URL);
         HttpResponse response = httpClient.execute(request);
         HttpEntity entity = response.getEntity();

         if (entity != null) {
             String result = EntityUtils.toString(entity);

             // Intentamos procesar la respuesta como JSON
             try {
                 JSONObject jsonObject = new JSONObject(result);
                 JSONArray resultsArray = jsonObject.getJSONArray("results");
                 
                 for (int i = 0; i < resultsArray.length(); i++) {
                     JSONObject estacion = resultsArray.getJSONObject(i);
                     
                     // Extraigo los datos que necesito
                     int numero = estacion.getInt("number");
                     String direccion = estacion.getString("address");
                     int disponibles = estacion.getInt("available");
                     int libres = estacion.getInt("free");
                     boolean abierto = estacion.getString("open").equalsIgnoreCase("T"); // Convertir 'T'/'F' a boolean
                     double lon = estacion.getJSONObject("geo_point_2d").getDouble("lon");
                     double lat = estacion.getJSONObject("geo_point_2d").getDouble("lat");
                     final String DELIMITER = ";"; // Define el delimitador

                    // --- Construir la cadena empaquetada y ASIGNARLA a values[i] ---
                    values[i] = numero + DELIMITER +
                                direccion + DELIMITER +
                                disponibles + DELIMITER +
                                libres + DELIMITER +
                                abierto + DELIMITER + // Guardar como "true" o "false"
                                lon + DELIMITER +
                                lat;

                     // Añadir los datos formateados al StringBuilder
                     datosBuilder.append("Estación: ").append(numero).append(" - ").append(direccion).append("\n");
                     datosBuilder.append("  Disponibles: ").append(disponibles).append("\n");
                     datosBuilder.append("  Libres: ").append(libres).append("\n");
                     datosBuilder.append("  Abierto: ").append(abierto ? "Sí" : "No").append("\n");
                     datosBuilder.append("  Coords: (Lon: ").append(lon).append(", Lat: ").append(lat).append(")\n");
                     datosBuilder.append("------------------------------------\n");
                 }

             } catch (org.json.JSONException e) {
                System.err.println("Error al procesar los datos JSON: " + e.getMessage());
                datosBuilder.append("\nError al procesar los datos JSON: ").append(e.getMessage()).append("\n");
                e.printStackTrace();
             }
         } else {
             datosBuilder.append("No se recibió respuesta de la API (entidad nula).\n");
         }

     } catch (IOException e) {
          System.err.println("Error de E/S al contactar la API: " + e.getMessage());
         datosBuilder.append("\nError de E/S al contactar la API: ").append(e.getMessage()).append("\n");
         e.printStackTrace();
     }
     
     // Asigna el contenido del StringBuilder a la variable 'datos'
     setDatos(datosBuilder.toString());
 }
 /**
 * @return the datos
 */
 public String getDatos() {
 return datos;
 }
 /**
 * @param datos the datos to set
 */
 public void setDatos(String datos) {
 this.datos = datos;
 }
 /**
 * @return the values
 */
 public String[] getValues() {
 return values;
 }
 /**
 * @param values the values to set
 */
 public void setValues(String[] values) {
 this.values = values;
 }
 /**
 * @return the numEst
 */
 public int getNumEst() {
 return numEst;
 }
 /**
 * @param numEst the numEst to set
 */
 public void setNumEst(int numEst) {
 this.numEst = numEst;
 }
}