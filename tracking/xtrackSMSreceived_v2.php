<?php

// trackSMSreceipt.php
//	Aplicación que recibe un SMS.

   // Me conecto a la BD
   require ("../db.php");

	// Obtengo los parametros enviados
   //$remitente  		 = $_GET['shortcode'];
   //$linea   		 = $_GET['msisdn'];
   //$mensajeSMS       = urldecode($_GET['body']);
   $remitente  		 = $_GET['remitente'];
   $linea   		 = $_GET['origen'];
   $mensajeSMS       = urldecode($_GET['mensaje']);

	// Obtengo el Redirect URL de la sección del click
   $query  = " INSERT INTO tbl_EvSMSReceived (remitente, msg, linea, fecharecepcion) ";
   $query .= " VALUES     ('".$remitente."','".$mensajeSMS."','".$linea."',getdate()); ";
   $result = mssql_query ($query);
   
   $posNombre = strpos($mensajeSMS, 'name');
   $posEspacio = strpos($mensajeSMS, ' ');
   $nombre = substr(strtolower($mensajeSMS),$posNombre+5,$posEspacio-($posNombre+5));
   
   $msgRespuesta = "Hola ".$nombre.", Circulo Lunar agradece tu inscripción!";
	$url = "http://www.exerwebsolutions.com/mobilesolutions/gateway.exr?shortcode=".$linea."&msisdn=" .$remitente. "&body=".urlencode($msgRespuesta);
     $archivoHTML = file($url);
    $contenido = "";
    $i_maxLineas = count($archivoHTML);
    $i = 0;
    while ($i<=$i_maxLineas)
    {
      $contenido .= chop($archivoHTML[$i])."\r\n";
      $i++;
    }	 

  //echo "<meta http-equiv='REFRESH' content='0;url=".$url."'>";
   echo $contenido;
   
  // echo "OK";
