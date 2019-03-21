<?php
header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

$numauth = $_GET['code'];;

if ($numauth == "") {
	$numauth = "21051975";
}


// trackCodeView.php
//	Aplicación que escribe en BD el view o código abierto, generado por el usuario.

   require ("dbSMS.php");
	// Obtengo los parametros enviados
   //$idUser  = $_GET['idUser'];
   //$idEnvio = $_GET['idEnvio'];
   $idUser  = 0;
   $idEnvio = 0;

   $query = " select count(*) as redimido, CAST(MAX(fechaRedencion) AS varchar) as fecha from track_CodeViews where ";
   $query .= " st_Code = '" . $numauth . "'";
   $query .= " and fechaRedencion IS NOT NULL;";
   $result = mssql_query ($query);
   $row = mssql_fetch_object($result);
   $redimido  = $row->redimido;
   $fecha= $row->fecha;

	// NO ha sido redimido
   if ($redimido == 0){
		   $query = " select count(*) as recibido from  tbl_EvSMSReceived ";
		   $query .= " WHERE msg = 'auth " . $numauth . "';";
		   $result = mssql_query ($query);
		   $row = mssql_fetch_object($result);
		   $recibido  = $row->recibido;
		   
		  $query = "INSERT INTO track_CodeViews (";
		  $query .= "st_Code,";
		  $query .= "fechaCodeView ";
		  $query .= " ) values ( ";
		  $query .= " '" . $numauth . "',";
		  $query .= " getdate());";
		  $result = mssql_query ($query);

		   if ($recibido > 0) {
			  $query  = " UPDATE track_CodeViews ";
			  $query .= " SET fechaRedencion = GETDATE() ";
			  $query .= " WHERE (st_Code = '" . $numauth . "');";
			  $result = mssql_query ($query);
				?>
			<span style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:11px; color:#666666;">El cup&oacute;n <strong><? echo $numauth; ?></strong> ya fue redimido el <?php echo date('d/m/Y H:i:s',strtotime($fecha)); ?></span>
				<?php
			  
		   } else {
				?>
				
			<img id="qrcode" src="http://qrcode.kaywa.com/img.php?s=6&d=SMSTO%3A5529634489%3Aauth%20<? echo $numauth; ?>" alt="qrcode" />   
			
				<?php
		   }

	} else {
	?>
<span style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:11px; color:#666666;">El cup&oacute;n <strong><? echo $numauth; ?></strong> ya fue redimido el <?php echo date('d/m/Y H:i:s',strtotime($fecha)); ?></span>
	<?php
   }
  ?>  


