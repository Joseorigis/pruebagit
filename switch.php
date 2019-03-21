<?php
	
	// TBD: Si no hay referer cerramos la app!!!
	
// HTML headers
	header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
	header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
	header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
	header ('Pragma: no-cache');	// HTTP/1.0


	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

	// INCLUDES & REQUIRES 
		include_once('includes/configuration.php');	// Archivo de configuración
		include_once('includes/database.class.php');	// Class para el manejo de base de datos
		include_once('includes/databaseconnection.php');	// Conexión a base de datos
		include_once('includes/functions.php');	// Librería de funciones


	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		session_start();
		
		

// --------------------
// INICIO CONTENIDO
// --------------------

		// Where to redirect...
		$switchtoredirect = "index.php";

		// Si hay JUMPTO...
		if (isset($_GET['q'])) {
		
				// If Already Logged In...
				if (isset($_SESSION[$configuration['appkey']])) {
					
							$switchto = "";
							$switchtokey = "";
					
							$switchto = urldecode(base64_decode(setOnlyText($_GET['q'])));
						
							// Obtengo el path del JumpTo
							$query  = "EXEC dbo.usp_app_UtilityCategoryElements
													'UserSwitchJumpTo', 
													'".$switchto."';";
							$dbsecurity->query($query);
							$my_row=$dbsecurity->get_row();
							
							$switchtokey 		= $my_row['ItemKey'];
							$switchtoredirect 	= $my_row['ItemPath'];
							
							$switchtoredirect .= "switch.php";
							$switchtoredirect .= "?i=".urlencode(base64_encode($_SESSION[$configuration['appkey']]['userid']));
							$switchtoredirect .= "&u=".urlencode(base64_encode($_SESSION[$configuration['appkey']]['username']));
							$switchtoredirect .= "&k=".urlencode(base64_encode($switchtokey));
							//$switchtoredirect .= "&k=".urlencode(base64_encode($configuration['appkey']));
					
				}
				
				//echo $switchtoredirect;
				header('Location: '.$switchtoredirect);
				exit();
		
		}
	
	
	
		// IF JumpFrom...
		if (isset($_GET['i'])) {

				// Get Params...
				$switchuid  = "";
				if (isset($_GET['i'])) { $switchuid  = urldecode(base64_decode(setOnlyText($_GET['i']))); }
				$switchuser = "";
				if (isset($_GET['u'])) { $switchuser = urldecode(base64_decode(setOnlyText($_GET['u']))); }
				$switchkey  = "";
				if (isset($_GET['k'])) { $switchkey  = urldecode(base64_decode(setOnlyText($_GET['k']))); }


				// Is ApplicationKey OK?
				if ($switchkey == $configuration['appkey']) {
					
					// If Already Logged In...
					if (!isset($_SESSION[$configuration['appkey']])) {

							// Do Special Login
							$loginerrorid = doLoginSwitch($switchuid, $switchuser, $switchkey);
							//echo "doLogin".$loginerrorid;
							
							// IF Login Error...
							if ($loginerrorid > 0) {
								require("loginwarning.php");
								header('Refresh: 10; URL=index.php');
								exit();
							}
					}
	
				} else {
					require("loginwarning.php");
					header('Refresh: 10; URL=index.php');
					exit();
					
				}

				// Redirect to HOME...
				header('Location: '.$switchtoredirect);
				exit();
		
		}
		
		
	// If something goes wrong, always come back to home....	
	header('Location: index.php');
	
	// END OF SCRIPT
	
?>