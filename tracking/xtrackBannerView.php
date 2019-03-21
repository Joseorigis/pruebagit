<?php

header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

if ($_GET['IDB'] && $_GET['CODE']) {

   require("../configuracion.php");
   $path = "root";
   require("../db.php");

$IDB  = $_GET['IDB'];
$CODE = $_GET['CODE'];

$Bexiste    = "SELECT * from tbl_Banners WHERE id_Banner = '$IDB' AND banner_code = '$CODE'";
$result     = mssql_query($Bexiste);

$SIexiste   = mssql_num_rows($result);

if ($SIexiste > 0) {

$DAT        = mssql_fetch_array($result);

$IDMEDIO    = $DAT['id_BannerMedio'];
$YOUIP      = $_SERVER['REMOTE_ADDR'];
$hoy        = getDate();
$fecha_now  = $hoy['mon']."/".$hoy['mday']."/".$hoy['year'];
$hora_now   = $hoy['hours'].":".$hoy['minutes'].":".$hoy['seconds'];


$MOUSEView = "INSERT INTO tbl_BannerViews (id_Banner, IP_view, fecha_view, hora_view)".
"VALUES ('$IDB','$YOUIP','$fecha_now','$hora_now')";
$result_01  = mssql_query($MOUSEView);

$COMPLETEimagen   = $DAT['imagen_url'];

$imagen   = $DAT['imagen_url'];

$Timage = explode(".", $imagen);

if ($Timage[1] == "jpg" || $Timage[1] == "JPG") { // IMAGEN JPG
$ITYPE = "image/jpeg";
}else if ($Timage[1] == "gif" || $Timage[1] == "GIF") {
$ITYPE = "image/gif";
}

// Mostramos la imagen deceada
   header('Content-type: '.$ITYPE);
   header('Content-transfer-encoding: binary');
   header('Content-length: '.filesize($imagen));
   readfile($imagen);


}else{
  // Una imagen predefinida en caso de que no exista la actual
   $imagen = 'noone.jpg';
   header('Content-type: image/jpeg');
   header('Content-transfer-encoding: binary');
   header('Content-length: '.filesize($imagen));
   readfile($imagen);
   exit();
}

}else{
header("Location: $webpageURL");
}

?>