<?php 

include_once('configuration.php');	// Archivo de configuración
include_once('functions.php');	// Librería de funciones
include_once('database.class.php');	// Class para el manejo de base de datos
include_once('databaseconnection.php');	// Conexión a base de datos

$sql = $_GET["query"]; // Consulta de Escape
$sql = str_replace("'", "''", $sql); // Preparamos la consulta
$query = "EXEC dbo.usp_fnc_IsValidSQL '".$sql."';"; //Validamos si la consulta es correcta
$dbconnection->query($query); // Activamos la conexión
$my_row = $dbconnection->get_row(); //Tomams el primer registro para validar 1 correcto 0 incorrecto

if($my_row["Result"] == 1){
	$query = "EXEC usp_app_AffiliationListsManage '".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."', 'listcount', '', '', '','','','','".$sql."';"; //Consultamos el SQL
	$dbconnection->query($query); // Activamos la conexión
	$items = $dbconnection->count_rows(); // Contamos el numero de registros
	echo number_format($items)." registros"; // Mostramos Número de registros
}else{
	echo "Error al consultar los registros!"; // Si la consulta no es correcta le decimos al usuario 
}

?>
