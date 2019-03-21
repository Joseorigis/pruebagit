<?php

// ----------------------------------------------------------------------------------------------------
// TRANSACTIONS EXPORT
// ----------------------------------------------------------------------------------------------------

// HTML headers
header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
header ('Pragma: no-cache');	// HTTP/1.0
header('Content-Type: application/json');


// WARNINGS & ERRORS
//ini_set('error_reporting', E_ALL&~E_NOTICE);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// INIT
// Iniciamos el controlador de SESSIONs de PHP
session_start();
// Obtengo el nombre del script en ejecución
$script = __FILE__;
$camino = get_included_files();
$scriptactual = $camino[count($camino)-1];


// INCLUDES & REQUIRES
include_once('../includes/configuration.php');	// Archivo de configuración
include_once('../includes/functions.php');	// Librería de funciones
include_once('../includes/database.class.php');	// Class para el manejo de base de datos
include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos



// --------------------
// INICIO CONTENIDO
// --------------------

// INIT
// ERROR ID ... inicializamos el indicador del error en el proceso
$actionerrorid = 0;
// AUTHNUMBER for duplicate check
$actionauth = getActionAuth();
$error = '0';
$errormessage = '';



// PARAMS
// cardnumber
$cardnumber = "";
if (isset($_GET['cardnumber'])) {
    $cardnumber = setOnlyNumbers($_GET['cardnumber']);
    //if (isValidNumber($cardnumber, "EAN13") == 0) {
    //	$actionerrorid = 2;
    //	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
    //}
}

if (isset($_GET['n'])) {
    $cardnumber = setOnlyNumbers($_GET['n']);
    //if (isValidNumber($cardnumber, "EAN13") == 0) {
    //	$actionerrorid = 2;
    //	$errormessage .= "&middot;&nbsp;El n&uacute;mero de tarjeta ingresado no es v&aacute;lido!<br />";
    //}
}

// action
$itemtype = 'add';
if (isset($_GET['t'])) {
    $itemtype = setOnlyLetters($_GET['t']);
    if ($itemtype == '') { $itemtype = 'add'; }
}
$itemtype = strtolower($itemtype);

// typereport
$typereport = 'ventasorbisfarma';
if (isset($_GET['typereport'])) {
    $typereport = setOnlyLetters($_GET['typereport']);
    if ($typereport == '') { $typereport = 'ventasorbisfarma'; }
}
$typereport = strtolower($typereport);



// FTP & FILENAME PARAMS
// FILENAME
$filename = "transacciones_".date('Ymd').".txt";

// FTP PARAMS
// Marzam
$ftphost = '148.244.187.136';
$ftpusr = 'sanofi';
$ftppwd = 'jhd8sk3DSD3kdn9';
// OrbisFarma
//$ftphost = 'settlement.orbisfarma.com.mx';
//$ftpusr = 'orbisfarmaftp';
//$ftppwd = '0rb!sf$rm$';

// EXPORT TO TXT
// GET RECORD...
$records = 0;
$query  = "SELECT CONVERT(VARCHAR,TransaccionId) + '|'
                  +CONVERT(VARCHAR,NumAutorizacion) + '|'
                  +Tarjeta + '|'
                  +Producto + '|'
                  +CONVERT(VARCHAR,Cantidad) + '|'
                  +CONVERT(VARCHAR,Descuento) + '|'
                  +CONVERT(VARCHAR,Bonificaciones) + '|'
                  +CONVERT(VARCHAR,FechaVenta) + '|'
                  +CONVERT(VARCHAR,NumTicket) AS RecordRow
                  ,ExportFile
          FROM [dbo].[SettlementExportFRAGUA] WITH(NOLOCK)
		  WHERE ExportFile LIKE '" . $typereport . "%';";
$dbtransactions->query($query);

// SET FILENAME
$filename = $dbtransactions->get_row()['ExportFile'].'.txt';

// GET TRANSACTIONS
$dbtransactions->query($query);
$records = $dbtransactions->count_rows();

// file to move:
$ftplocalfile = $filename;
$ftpdestinationfile = '/IN/'.$filename;

// If recors process...
if ($records > 0) {

    $f = fopen($filename, "w"); // Open the text file

    while($my_row=$dbtransactions->get_row()){
        //echo $my_row['RecordRow']."<br>";
        // Write text line
        fwrite($f, $my_row['RecordRow']."\r\n");

    }

    fclose($f); // Close the text file

    //// Open file for reading, and read the line
    //$f = fopen("textfile.txt", "r");
    //echo fgets($f);
    //fclose($f);

}



// EXPORT TO FTP
// If records to process...
if ($records > 0) {

    // Open FTP Connection
    $ftpconnection = ftp_connect($ftphost);
try{
    // Open FTP Connection Login
    $ftplogin = ftp_login($ftpconnection, $ftpusr, $ftppwd);

    // Check if connection OK
    if (!$ftpconnection || !$ftplogin) {
        //echo json_encode(array("Error" => "401","Message"=>"Connection attempt failed!"));
        $error = '401';
        $errormessage = 'Connection attempt failed!';
        $InteractionSubject 		= "ERROR  Settlement @ Fragua [8]";
        $InteractionContentText		= "El archivo generado no pudo ser publicado: ".$filename;
        //die('Connection attempt failed!');
    }

    // Set connection to passive
    ftp_pasv($ftpconnection, true);

    // Upload file
    $ftpupload = ftp_put($ftpconnection, $ftpdestinationfile, $ftplocalfile, FTP_ASCII);
}
catch(Exception $e)
{
    ftp_close($ftpconnection);

    // Delete publish file
    unlink($ftplocalfile);
}
    // If uploaded...
    if (!$ftpupload) {
        //echo json_encode(array("Error" => "201","Message"=>"FTP upload failed!"));
        $error = '201';
        $errormessage = 'FTP upload failed!';
        //echo 'FTP upload failed!';
        $InteractionSubject 		= "ERROR  Settlement @ Fragua [8]";
        $InteractionContentText		= "El archivo generado no pudo ser publicado: ".$filename;

        // ENVIO DE EMAIL DE NOTIFICACION
        $InteractionId				= $ftpupload;
        $InteractionFrom  			= "settlement@orbisfarma.com.mx";
        $InteractionFromName		= "OrbisFarma<settlement@orbisfarma.com.mx>";
        $InteractionReplyTo 		= "";
        //$InteractionContent 			= "templates/MessageTemplate.html";
        //$InteractionContent 			= "http://www.mazsalud.com.mx/preproduccion/emailing/callcenter/MessageTemplate.html";
        $EmailDistributionList		= "helpdesk@orbisfarma.com.mx";
        $EmailDistributionListCc	= "";
        $EmailDistributionListBCc	= "davidfr@origis.com";

        // CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
        // Contenido
        $InteractionCode 		= "Settlement.OrbisFarma.".$InteractionId.".".date("YmdHis");
        $InteractionCodeAuth 	= md5($InteractionCode);
        $InteractionCodeUnique  = "-@id:".$InteractionCode."-";

        // EMAIL HEADERS
        $EmailMessage['Headers'] = "";
        $EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
        $EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$InteractionCode."\r\n";
        $EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$InteractionCodeAuth."\r\n";

        // To, From & Subject del Email
        $EmailTo 		= $EmailDistributionList;
        $EmailCc		= $EmailDistributionListCc;
        $EmailBcc 		= $EmailDistributionListBCc;

        // EMAIL FROM & TO
        $EmailMessage['From'] 	  = $InteractionFrom;
        $EmailMessage['FromName'] = $InteractionFromName;
        $EmailMessage['To']   	  = $EmailTo;
        $EmailMessage['ReplyTo']  = $InteractionReplyTo;
        $EmailMessage['Cc']  	  = $EmailCc;
        $EmailMessage['Bcc']  	  = $EmailBcc;

        // EMAIL SUBJECT
        $EmailMessage['Subject'] = $InteractionSubject;

        // EMAIL CONTENT
        //$EmailMessage['Content'] = $InteractionContent;

        // REGISTRANT CONTENT
        $EmailMessage['Body'] = $InteractionContentText;

        $EmailMessage['Attachment'] = "../includes/".$ftplocalfile;
        // --------------------------------------------------
        // INTERACTION SEND!!!
        // --------------------------------------------------
        require_once('smtp/class.sendmail.php');
        // Interpretar respuesta para el OK
        // Reintentos?
        $EmailMessageSent = 0;
        $EmailMessageSentLog = "";
        $actionerrorid = 0;

        // Instanciamos un objeto de la clase sendmail
        $mail = new sendmail('smtpconnection0');

        // Enviamos notificación de de publicación de archivo.
        $EmailMessageSent = $mail->mail($EmailMessage['From'],
                                        $EmailMessage['FromName'],
                                        $EmailMessage['ReplyTo'],
                                        $EmailMessage['To'],
                                        $EmailMessage['Cc'],
                                        $EmailMessage['Bcc'],
                                        $EmailMessage['Subject'],
                                        $EmailMessage['Body'],
                                        $EmailMessage['Headers'],
                                        $EmailMessage['Attachment']);

        if ($EmailMessageSent) {
            $InteractionResult = 'OK;';
            $InteractionSent = 1;
        } else {
            $InteractionResult = 'PHPError;';
            $InteractionSent = 0;
        }
    } else {
        //echo json_encode(array("Error" => "0","Message"=>"Proceso Terminado."));
        $error = '0';
        $errormessage = 'Proceso Terminado.';
        //echo 'Proceso Terminado.';
        $InteractionSubject 		= "Settlement @ Fragua [8]";
        $InteractionContentText		= "Ya se encuentra en el ftp el siguiente archivo publicado: ".$filename;
    }




    //echo $InteractionResult .' - '.$InteractionSent;
    echo json_encode(array("Error" => "$error","Message"=>"$errormessage","SendEmail" => "$InteractionSent","EmailMessage"=>"$InteractionResult!"));


    //echo json_encode(array("Error" => "$error","Message"=>"$errormessage"));

    // Close FTP Connection
    ftp_close($ftpconnection);

    // Delete publish file
    unlink($ftplocalfile);

}


// DATABASE CONNECTION CLOSE
//include_once('includes/databaseconnectionrelease.php');

?>
