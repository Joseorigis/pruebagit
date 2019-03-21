<?php

// NO CACHE
header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
// Session
session_start();


   // Database
   require ("../db.php");
   // Configuración
   require ("../configuracion.php");

	// Obtengo el nombre del script en ejecución
	$script = __FILE__;
   $path = "root";
  

	// PARAMS
		// Full querystring
	   $querystring = (!empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");	

		// Inicializo
	   $idBanner    		 = 0;
	   $codeBanner  		 = 0;
	   $idUser				 = 0;

		// Obtengo los parametros enviados
	   $idBanner  		 = $_GET['IDB'];
	   $codeBanner 		 = $_GET['CODE'];

		// Validamos
	   // Si no enviaron parametros, pongo como si fueran CERO
	   if ($idBanner == "") { $idBanner  = 0; }
	   if ($codeBanner == "") { $codeBanner  = 0; }


	// SECTION
		// Obtengo el Redirect URL de la sección del click
	   $query = " SELECT * FROM tbl_Banners WITH(NOLOCK) WHERE (id_Banner = '" . $idBanner . "') AND (banner_code = '" . $codeBanner . "'); ";
	   $result = mssql_query ($query);
	   $row = mssql_fetch_object($result);
	   $seccionRedirect = $row->redirect_url;
	
	   // Si la sección no existe, la redirijo al Home o liga principal del programa
	   if ($seccionRedirect == "") { 
			 $query = " SELECT sectionRedirectURL FROM track_EmailSections WITH(NOLOCK) WHERE (id_EmailSection = 1); ";
			 $result = mssql_query ($query);
			 $row = mssql_fetch_object($result);
			 $seccionRedirect = $row->sectionRedirectURL;
	   }


	// PREVIOUS CLICK
	   // Si no ha sido guardado con anterioridad, se inserta el click
		  $query  = "INSERT INTO tbl_BannerClicks (";
		  $query .= "id_UsuarioWeb,";
		  $query .= "id_Banner,";
		  $query .= "phpsessionid, ";
		  $query .= "apptracking, ";
		  $query .= "IP_click, ";
		  $query .= "fechaClick ";
		  $query .= " ) VALUES ( ";
		  $query .= " '" . $idUser . "',";
		  $query .= " '" . $idBanner . "',";
		  $query .= " '" . session_id() . "',";
		  $query .= " '" . $script . "',";
		  $query .= " '" . $_SERVER['REMOTE_ADDR'] . "',";
		  $query .= " getdate());";
		  $result = mssql_query ($query);


	// REDIRECT FINAL
		// Asignamos el querystring enviado, tal y como lo recibimos
		//$seccionRedirect .= "?".$querystring;

		// Redirigimos hacia el URL de la sección
	   header("Refresh: 0;url=$seccionRedirect");
	   //echo "<meta http-equiv='REFRESH' content='0;url=".$seccionRedirect."'>";

?>