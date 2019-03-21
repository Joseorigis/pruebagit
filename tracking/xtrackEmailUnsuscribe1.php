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

// trackEmailNoView.php
//	Aplicación que escribe en BD el click generado por el usuario en el email y,
//  genera un versión de la pieza enviada, en web, ya que el click proviene de un No View.
//	Esta versión, cuenta el click de manera única.

	// PARAMS
		// Full querystring
	   $querystring = (!empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");	

		// Inicializo
	   $idUser    		 = 0;
	   $idEnvio  		 = 0;
	   $idEmailSection   = 0;
	   $email			 = "";

		// Obtengo los parametros enviados
		if (isset($_GET['idUser']))		{ $idUser = $_GET['idUser']; }
		if (isset($_GET['idEnvio']))	{ $idEnvio = $_GET['idEnvio']; }
		if (isset($_GET['idSection'])) 	{ $idEmailSection = $_GET['idSection']; }
		if (isset($_GET['email']))		{ $email = $_GET['email']; }

		// Validamos
	   // Si no enviaron parametros, pongo como si fueran CERO
	   if ($idUser  == "|USERID|" || $idUser == "") { $idUser  = 0; }
	   if ($idEnvio == "|ENVIOID|" || $idEnvio == "") { $idEnvio  = 0; }
	   // Si no son números
	   if (!is_numeric($idUser))		 { $idUser = 0; }
	   if (!is_numeric($idEnvio))		 { $idEnvio = 0; }
	   if (!is_numeric($idEmailSection)) { $idEmailSection = 1; }
	   // Caracteres Raros
	   if (strpos($querystring, "'") !== false) { $querystring = ""; }


	// PREVIOUS CLICK
	   // Verifico que si el click ya fue almacenado en la BD
	   $query  = " SELECT COUNT(id_UsuarioWeb) AS previos ";
	   $query .= " FROM track_EmailClicks WITH(NOLOCK) ";
	   $query .= " WHERE  id_UsuarioWeb = '" . $idUser . "'";
	   $query .= " AND    id_EnvioEmail = '" . $idEnvio . "'";
	   $query .= " AND    id_EmailSection = '" . $idEmailSection . "'";
	   $dbconnection->query($query);
	   $row=$dbconnection->get_row();
	   $previos  = $row['previos'];

	   // Si no ha sido guardado con anterioridad, se inserta el click
	   if ($previos == 0) {
		  $query  = "INSERT INTO track_EmailClicks (";
		  $query .= "id_UsuarioWeb,";
		  $query .= "id_EnvioEmail,";
		  $query .= "id_EmailSection,";
		  $query .= "phpsessionid, ";
		  $query .= "apptracking, ";
		  $query .= "fechaClick ";
		  $query .= " ) VALUES ( ";
		  $query .= " '" . $idUser . "',";
		  $query .= " '" . $idEnvio . "',";
		  $query .= " '" . $idEmailSection . "',";
		  $query .= " '" . session_id() . "',";
		  $query .= " '" . $script . "',";
		  $query .= " getdate());";
		  $dbconnection->query($query);
	   }


	// TRACKING VIEW
	   // Genero la escritura de email abierto, en caso de que no este todavía
	   include_once("trackEmailView.php");


	// REDIRECT FINAL
		// Asignamos el querystring enviado, tal y como lo recibimos
		$seccionRedirect  = "webEmailUnsuscribe.php";
		$seccionRedirect .= "?".$querystring;
	    //$seccionRedirect .=  "?idUser=" . $idUser . "&idEnvio=".$idEnvio."";
		
		// Redirigimos hacia el URL de la sección
	   header("Refresh: 0;url=$seccionRedirect");
	   //echo "<meta http-equiv='REFRESH' content='0;url=".$seccionRedirect."'>";

?>
