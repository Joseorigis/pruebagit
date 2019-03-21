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
	if(isset($_POST['q'])) {
	
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
				
				include_once('../includes/databaseconnectiontransactions.php');
	
				$connectionname = trim($_POST['q']);

				$itemscount = 0;
				$query  = " EXEC dbo.usp_app_HelpDeskConnectionsSearch
									'list', '0', '', '".$connectionname."', 'connections', 'exact';";
				$dbtransactions->query($query);
				$itemscount = $dbtransactions->count_rows();

				if($itemscount > 0)	{
						echo '<font color="red"><em>La conexi&oacute;n ';
						echo '<a href="?m=helpdesk&s=connections&a=view&q='.$connectionname.'" title="Ver Conexiones" target="_BLANK">';
						echo '<STRONG>'.$connectionname.'</STRONG></a> ';
						echo 'podr&iacute;a ya existir en las conexiones!.</em></font> ';
						echo '<a href="?m=helpdesk&s=connections&a=view&q='.$connectionname.'" title="Ver Conexiones" target="_BLANK">Ver Conexiones</a>';
				} else	{
					echo 'OK';
				}

	}

?>