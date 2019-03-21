<?php

// HTML headers
	header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
	header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
	header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
	header ('Pragma: no-cache');	// HTTP/1.0


	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');


	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		if(!session_id()){
			// Session
			session_start();
		}
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
		
	
	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/functions.php');	// Librería de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos
		
// --------------------
// INICIO CONTENIDO
// --------------------

// trackEmailViews.php
//	Aplicación que escribe en BD el view o email abierto, generado por el usuario.
//	Esta versión, cuenta únicamente la primera vez que el email es abierto.


	// IMAGE
	   // Primero desplegamos la imagen para no fallar
	   //$imagen = "../images/trademark.gif";
	   //$imagen = "../images/spacer.gif";
	   //header('Content-type: image/gif');
	   //header('Content-transfer-encoding: binary');
	   //header('Content-length: '.filesize($imagen));
	   //readfile($imagen);

	// PARAMS
		// Inicializo
	   $idUser    		 = 0;
	   $idEnvio  		 = 0;

		// Obtengo los parametros enviados
		if (isset($_GET['idUser']))		{ $idUser = $_GET['idUser']; }
		if (isset($_GET['idEnvio']))	{ $idEnvio = $_GET['idEnvio']; }

		// Validamos
	   // Si no enviaron parametros, pongo como si fueran CERO
	   if ($idUser  == "|USERID|" || $idUser == "") { $idUser  = 0; }
	   if ($idEnvio == "|ENVIOID|" || $idEnvio == "") { $idEnvio  = 0; }
	   // Si no son números
	   if (!is_numeric($idUser))		 { $idUser = 0; }
	   if (!is_numeric($idEnvio))		 { $idEnvio = 0; }


	// PREVIOUS VIEW
	   // Verifico que si el VIEW ya fue almacenado en la BD
	   $query  = " SELECT COUNT(id_UsuarioWeb) AS previos ";
	   $query .= " FROM track_EmailViews WITH(NOLOCK) ";
	   $query .= " WHERE  id_UsuarioWeb = '" . $idUser . "'";
	   $query .= " AND    id_EnvioEmail = '" . $idEnvio . "'";
	   $dbconnection->query($query);
	   $row=$dbconnection->get_row();
	   $previos  = $row['previos'];
   
	   // Si no ha sido guardado con anterioridad, se inserta el VIEW
	   if ($previos == 0) {
			  $query  = "INSERT INTO track_EmailViews (";
			  $query .= "id_UsuarioWeb,";
			  $query .= "id_EnvioEmail,";
			  $query .= "phpsessionid, ";
			  $query .= "apptracking, ";
			  $query .= "fechaView ";
			  $query .= " ) VALUES ( ";
			  $query .= " '" . $idUser . "',";
			  $query .= " '" . $idEnvio . "',";
			  $query .= " '" . session_id() . "',";
			  $query .= " '" . $script . "',";
			  $query .= " getdate());";
			  $dbconnection->query($query);
	   }

?>
