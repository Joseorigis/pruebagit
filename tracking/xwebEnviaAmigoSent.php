<?php
header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

require("../db.php");

$idUser    		= $_GET['idUser'];
$idEnvio   		= $_GET['idEnvio'];
$emailpara[1] 	= $_GET['email1'];
$emailpara[2] 	= $_GET['email2'];
$emailpara[3] 	= $_GET['email3'];

// Datos Campaña
 $query  = " SELECT tbl_CampaniasEmail.* ";
 $query .= " FROM tbl_CampaniasEmail INNER JOIN tbl_CampaniasEmailEnviadas ";
 $query .= " ON tbl_CampaniasEmail.id_CampaniaEmail = tbl_CampaniasEmailEnviadas.id_CampaniaEmail";
 $query .= " WHERE (tbl_CampaniasEnviosEmail.id_EnvioEmail = '".$idEnvio."');";
 $result = mssql_query($query);
 $numrows = mssql_num_rows($result);
 
 if ($numrows > 0){
 
	$row = mssql_fetch_object($result);
	
	$remitenteEmail 	   = $row->st_EmailRemitente;
	$remitenteNombre	   = $row->st_NombreRemitente;
	$remitenteEmailReplyTo = $row->st_EmailReplyTo;
	$asunto 			   = $row->st_Asunto;
	$contenidoHTML  	   = $row->tx_Contenido;

	// Preparamos la pieza
	$from_nombre = $remitenteNombre;
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";	
	$headers .= "From: ".$remitenteNombre." <".$remitenteEmail.">\r\n";
	$headers .= "Reply-To: ".$remitenteNombre." <".$remitenteEmailReplyTo.">\r\n";
	$headers .= "X-CRM-Envio-id: ".$idEnvio."\r\n";
	$headers .= "X-CRM-UsuarioWeb-id: 0\r\n";
	$headers .= "X-CRM-Email-id: CRM MX\r\n";
	$headers .= "X-CRM-Email-Authentication: 0\r\n";

	$from = $remitenteEmail;
	$subject = $asunto;
	
	$html = implode('', file($contenidoHTML));
	$html = str_replace("<#USERID>", "0",$html);
	$html = str_replace("<#IDENVIO>", $idEnvio,$html);
	$html = str_replace("<#NOMBRE>", "",$html);
	$html = str_replace("<#PATERNO>", "",$html);
	$html = str_replace("<#MATERNO>", "",$html);
	$body = $html;
	
	for ($indice = 1; $indice < 4; $indice++) {	
		if ($emailpara[$indice] <> '') {
			mail($emailpara[$indice], $subject, $body, $headers);
			echo " E-mail enviado a ".$emailpara[$indice]."!<br>";
		}	
	}		

	// Inserto el evento
		$queryInsert  = " INSERT INTO tbl_EvEnviaAmigoUsuariosWeb ";
		$queryInsert .= " (id_UsuarioWeb, id_Envio, dt_FechaEnvio,  ";
		$queryInsert .= " st_EmailDestino1, st_EmailDestino2, st_EmailDestino3) VALUES ";
		$queryInsert .= " (".$idUser.",".$idEnvio.", GETDATE(), ";
		$queryInsert .= " '".$emailpara[1]."','".$emailpara[2]."','".$emailpara[3]."'); ";
		//$queryInsert .= " INSERT INTO tbl_EventosUsuariosWeb (id_UsuarioWeb, id_TipoEvento, id_Evento, dt_FechaEvento, dt_HoraEvento, st_NombreEvento) VALUES  ";
		//$queryInsert .= " (".$idusuario.", 5,@@identity,getdate(),getdate(),'".$asunto."'); ";
		$resInsert    = mssql_query ($queryInsert);
		
	
}
mssql_close();
?>