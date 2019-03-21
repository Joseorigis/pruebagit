<?php
/**
*
* TYPE:
*	AJAX REFERENCE
*
* security_users_newcheck.php
* 	Verifica que el usuario o email no existan previamente.
*
* @version 
*
*/

// --------------------
// INICIO CONTENIDO
// --------------------

	// username
	if(isset($_POST['cardnumber'])) {
	
				// Verificamos la página que se esta navegando
				if (!isset($appcontainer)) {
					
					// Obtengo el nombre del script en ejecución
						$script = __FILE__;
						
					// Iniciamos el controlador de SESSIONs de PHP
						session_start();
					
					// INCLUDES & REQUIRES
						include_once('../includes/configuration.php');	// Archivo de configuración
						include_once('../includes/database.class.php');	// Class para el manejo de base de datos
						include_once('../includes/databaseconnection.php');	// Conexión a base de datos
						include_once('../includes/functions.php');	// Librería de funciones
					
				} 
	
				$cardnumber = trim($_POST['cardnumber']);

				// Verificamos el usuario
				$query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemManage
									'check', 
									'crm',
									'0',
									'".$script."',
									'0',
									'".$cardnumber."';";
				$dbconnection->query($query);
				$my_row=$dbconnection->get_row();
				$ErrorId	= $my_row['Error']; // Total Usuarios

				if($ErrorId > 0)	{
					if($ErrorId == 202)	{
						echo '<font color="red"><em>La tarjeta ';
						echo '<a href="?m=affiliation&s=items&a=view&q='.$cardnumber.'" title="Ver Afiliado" target="_BLANK">';
						echo '<STRONG>'.$cardnumber.'</STRONG></a> ';
						echo 'ya est&aacute; afiliada!.</em></font> ';
						echo '<a href="?m=affiliation&s=items&a=view&q='.$cardnumber.'" title="Ver Afiliado" target="_BLANK">Ver Afiliado</a>';
					} else	{
						echo '<font color="red"><em>La tarjeta ';
						echo '<STRONG>'.$cardnumber.'</STRONG> ';
						echo 'no es v&aacute;lida para afiliar!.</em></font> ';
					}
				} else	{
					echo 'OK';
				}

	}

?>