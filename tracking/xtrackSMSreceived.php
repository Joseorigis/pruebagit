<?php

header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

// trackSMSreceipt.php
//	Aplicación que recibe un SMS.

   // Me conecto a la BD
   require ("dbSMS.php");

	// Obtengo los parametros enviados
   //$remitente  		 = $_GET['shortcode'];
   //$linea   		 = $_GET['msisdn'];
   //$mensajeSMS       = urldecode($_GET['body']);
   $remitente  		 = $_GET['remitente'];
   $linea   		 = $_GET['origen'];
   $mensajeSMS       = urldecode($_GET['mensaje']);

	// Obtengo el Redirect URL de la sección del click
   $query  = " INSERT INTO tbl_EvSMSReceived (remitente, msg, linea, fecharecepcion, asignacion) ";
   $query .= " VALUES     ('".$remitente."','".$mensajeSMS."','".$linea."',getdate(), ''); ";
   $result = mssql_query ($query);
   
   echo "OK";
?>
