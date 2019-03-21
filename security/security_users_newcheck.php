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
	if(isset($_POST['username'])) {
		
				// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones

				$username = setOnlyText($_POST['username']);
		
				$sessionuser = "0";
				if (isset($_SESSION[$configuration['appkey']]['userid'])) { $sessionuser = $_SESSION[$configuration['appkey']]['userid']; }

				// Verificamos el usuario
				$query  = "EXEC dbo.usp_app_SecurityUserManage 
									'".$_SESSION[$configuration['appkey']]['userid']."',
									'".$configuration['appkey']."',
									'check', 
									'0',
									'".$username."';";
				$dbsecurity->query($query);
				$my_row=$dbsecurity->get_row();
				$ErrorId	= $my_row['Error']; // Total Usuarios

				if($ErrorId > 0)	{
					echo '<font color="red"><em>El nombre de usuario <STRONG>'.$username.'</STRONG> no est&aacute; disponible!.</em></font>';
				} else	{
					echo 'OK';
				}

	}

	// email
	if(isset($_POST['email'])) {
	
				// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones

				$email = trim($_POST['email']);
				if (isValidEmail($email) == 0) {
					$email = '@';
				}
		
				$sessionuser = "0";
				if (isset($_SESSION[$configuration['appkey']]['userid'])) { $sessionuser = $_SESSION[$configuration['appkey']]['userid']; }

				// Verificamos el usuario
				$query  = "EXEC dbo.usp_app_SecurityUserManage 
									'".$_SESSION[$configuration['appkey']]['userid']."',
									'".$configuration['appkey']."',
									'check', 
									'0',
									'',
									'',
									'',
									'0',
									'',
									'',
									'".$email."';";
				$dbsecurity->query($query);
				$my_row=$dbsecurity->get_row();
				$ErrorId	= $my_row['Error']; // Total Usuarios

				if($ErrorId > 0)	{
					echo '<font color="red"><em>El email <STRONG>'.$email.'</STRONG> ya ha sido registrado con anterioridad!.</em></font>';
				} else	{
					echo 'OK';
				}

	}

?>