<?php
/*
Dieses Tool übersetzt zwischen Betriebsstellennamen und DS100-Abkürzungen.
Grundsätzlich erfolgt die Übertragung von Daten mittels $_GET.

Variablen:
api_handle : Handle für die DB-API
api_httpheaders : HTTP-Header für die DB-API
api_output : Ausgabe der DB-API
api_url : URL für die DB-API
bst : Input Betriebsstelle
bst_index : Zählvariable für die Betriebsstelle
bst_input : Eingabe der Betriebsstelle
bst_out : Output Betriebsstelle
db_authbearer : Zugangstoken der DB-API
db_cons_secret : Consumer Secret der DB-API
db_cons_key : Consumer Key
ds100 : Input DS100-Abkürzungen
ds100_out : Output DS100-Abkürzungen

*/
$title = "DS100-Übersetzer" ;
$lizenz = "ccby" ;

include ( "header.php" ) ;

# Zugangsdaten zur DB-API einbinden
include ( "../db_api.php" ) ;


If ( ! empty ( $_GET [ "ds100" ] ) ) {
	$ds100 = $_GET  [ "ds100" ] ;
}
If ( ! empty ( $_GET [ "bst" ] ) ) {
	$bst_input = $_GET [ "bst" ] ;
	$bst = $bst_input ;
# Umlaute und Sonderzeichen ersetzen
	$bst = str_replace ( "Ä" , "%C3%84" , $bst ) ;
	$bst = str_replace ( "Ö" , "%C3%96" , $bst ) ;
	$bst = str_replace ( "Ü" , "%C3%9C" , $bst ) ;
	$bst = str_replace ( "ä" , "%C3%a4" , $bst ) ;
	$bst = str_replace ( "ö" , "%C3%b6" , $bst ) ;
	$bst = str_replace ( "ü" , "%C3%bc" , $bst ) ;
	$bst = str_replace ( "ß" , "%C3%9f" , $bst ) ;
	$bst = str_replace ( " " , "%20" , $bst ) ;
}

echo "<p>Dieses Tool bietet die Möglichkeit, die Bedeutung der DS-100 Abkürzungen übersetzen zu lassen." ; # Titelzeile anzeigen

If ( isset ( $ds100 ) && isset ( $bst ) ) { 
	# Fehlermeldung ausgeben, wenn BEIDE Variablen gesetzt sind
	echo "<p><focus>Bitte gib entweder die DS-100-Abkürzung oder die Betriebsstellenbezeichnung ein.</focus></p>" ;
	# Formular darstellen
	echo "<form method=\"GET\">";
	echo "<table>" ;
	echo "<tr><td>DS-100-Abkürzung</td><td><input type=\"text\" name =\"ds100\" value = \"$ds100\" /></td></tr>" ;
	echo "<tr><td>Betriebsstellenbezeichner</td><td><input type=\"text\" name=\"bst\" value = \"$bst\" /></td></tr>" ;
	echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"absenden\"></td></tr>" ;
	echo "</table>" ;
	echo "</form>" ;
}

If ( isset ( $ds100 ) && ! isset ( $bst ) ) { 
	# Übersetzung DS100 → BST
	# API-Anfrage
	$api_url = "https://api.deutschebahn.com/betriebsstellen/v1/betriebsstellen/" . $ds100 ;
	$api_handle = curl_init () ;
	curl_setopt ( $api_handle , CURLOPT_URL , $api_url ) ;
	$api_httpheaders = array ( "Accept: application/json" , "Authorization: Bearer " . $db_authbearer ) ;
	curl_setopt ( $api_handle , CURLOPT_HTTPHEADER , $api_httpheaders ) ;
	curl_setopt ( $api_handle , CURLOPT_HEADER , FALSE ) ;
	curl_setopt ( $api_handle , CURLOPT_RETURNTRANSFER , TRUE ) ;
	$api_output = curl_exec ( $api_handle ) ;
	curl_close ( $api_handle ) ;
	# Daten extrahieren
	$ds100_out = json_decode ( $api_output , TRUE ) ;
	# Daten ausgeben
	echo "<p>Für das Betriebsstellenkürzel <b>" . $ds100 . "</b> wurden folgende Ergebnisse gefunden:</p>" ;
	echo "<table>" ;
	echo "<tr><th>#</th><th>Betriebsstelle</th><th>DS-100-Abkürzung</th></tr>" ;
	echo "<tr><td>1</td><td>" . $ds100_out [ 'name' ] . "</td><td>" . $ds100_out [ 'abbrev' ] . "</td></tr>" ;	
	echo "</table>" ;
	echo "<p><a href=\"https://www.simon-bredemeier.de/ds100.php\">zur Eingabemaske</a>" ; 	
}

If ( ! isset ( $ds100 ) && isset ( $bst ) ) { 
	# Übersetzung BST → DS 100
	# API-Anfrage
	$api_url = "https://api.deutschebahn.com/betriebsstellen/v1/betriebsstellen/?name=" . $bst ;
	$api_handle = curl_init () ;
	curl_setopt ( $api_handle , CURLOPT_URL , $api_url ) ;
	$api_httpheaders = array ( "Accept: application/json" , "Authorization: Bearer " . $db_authbearer ) ;
	curl_setopt ( $api_handle , CURLOPT_HTTPHEADER , $api_httpheaders ) ;
	curl_setopt ( $api_handle , CURLOPT_HEADER , FALSE ) ;
	curl_setopt ( $api_handle , CURLOPT_RETURNTRANSFER , TRUE ) ;
	$api_output = curl_exec ( $api_handle ) ;
	curl_close ( $api_handle ) ;	
	# Daten extrahieren
	$bst_out = json_decode ( $api_output , TRUE ) ;
	# Daten ausgeben
	echo "<p>Für die Betriebsstelle <b>" . $bst_input . "</b> wurden folgende Ergebnisse gefunden:</p>" ;
	echo "<table>" ;
	echo "<tr><th>#</th><th>Betriebsstelle</th><th>DS-100-Abkürzung</th></tr>" ;
	for ( $i = 0 ; $i <= ( count ( $bst_out ) - 1 ) ; $i++ ) {	
		echo "<tr><td>" . ( $i + 1 ) . "</td><td>" . $bst_out [ $i ] [ 'name' ] . "</td><td>" . $bst_out [ $i ] [ 'abbrev' ] . "</td></tr>" ;	
	}
	echo "<tr><td> </td><td>" . $bst_out [ 'name' ] . "</td><td>" . $bst_out [ 'abbrev' ] . "</td></tr>" ;	
	echo "</table>" ;
	echo "<p><a href=\"https://www.simon-bredemeier.de/ds100.php\">zur Eingabemaske</a>" ; 
}

If ( ! isset ( $ds100 ) && ! isset ( $bst ) ) { 
	# Leeres Formular anzeigen
	# Formular darstellen
	echo "<form method=\"GET\">";
	echo "<table>" ;
	echo "<tr><td>DS-100-Abkürzung</td><td><input type=\"text\" name =\"ds100\" /></td></tr>" ;
	echo "<tr><td>Betriebsstellenbezeichner</td><td><input type=\"text\" name=\"bst\" /></td></tr>" ;
	echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"absenden\"></td></tr>" ;	
	echo "</table>" ;
	echo "</form>" ;
}
echo "<p>Die verwendeten Daten stellt die Deutsche Bahn unter <a href=\"https://developer.deutschebahn.com/store/apis/info?name=Betriebsstellen&version=v1&provider=DBOpenData\">diesem Link</a> zur Verfügung.</p>" ;
include ( "footer.php" ) ;
echo "</body>" ;
echo "</html>" ;
?>