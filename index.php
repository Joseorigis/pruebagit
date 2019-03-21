<?php
/**
*
* index.php
* Script principal y contenedor de toda la ejecución del aplicación.
* Parsea todo lo recibido como parámetros y genera el código a través de includes y requires.
*
* @copyright  Copyright (c) 2011 Origis Loyalty
* @version    v20110906
*
*/

	// PARAM TYPES
	// --------------------
	// m : modulo
	// s : section
	// a : action
	// q : query en busqueda
	// n : id o numero enviado
	// t : type of query en busqueda


	// PAGE TYPES
	// --------------------
	// INDEX REFERENCE : invocada por el index
	// SIDEBAR : invocada por la página actual
	// IFRAME REFERENCE : invocada dentro de un iframe
	// AJAX REFERENCE : invocada por un ajax
	// INCLUDE REFERENCE : librerías o funciones
	

// PENDIENTES
	// TBD: SESSION se pierde?, o funciona?
	// VERIFICA ESTILOS CSS, esos quieres?
	// Historial de navegación dinámico!!!, barra de navegación	 ADD.. que paso con el path de naveagción, no se ve bien
	// TBD: Agregar domain a cookies
	// TBD: valida bien el store de userstatus, sus escenarios
	// TBD: termina login escenarios
	// TBD: si un dato no esta, nadar vacio y poner el valor default de No ha proporcionado, incluso con estilo directo
	// TBD: Log de emails enviados a USERs, en tablas o algo así, como tbl_EvEmailing...
	// TBD: templates a includes?
	// TBD: Recientes y Frecuentes, cambialo, Recientes: Tus ultimas acciones del login previo; Frecuentes: tus acciones recurrentes...
	//			calculado de la frecuencia de logins, vemos si es a 7, 14 o 28 días, si no has loggeado, frecuentes de otros
	// TBD: Para Recientes y Frecuentes, como activamos el nombre del link?
	// TBD: Para indexar o nombrar recientes y frecuentas, hacemos tabla de archivos y su nombre?, tal vez mover el navigation log a cada archivo?
	// TBD: Cuando timeout, marcar el logout en el loginform
	// TBD: Activar el ANSI para stores?
	
	// TBD: CSS para input, pero quita botones ordinarios...

// HTML headers
	header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
	header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
	header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
	header ('Pragma: no-cache');	// HTTP/1.0
	//header ('X-Frame-Options: DENY');
	header ('X-Frame-Options: SAMEORIGIN');
    ini_set('session.cookie_httponly', 1);


	// WARNINGS & ERRORS
		ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
        //ini_set('session.cookie_httponly', 1);
        //ini_set('session.cookie_secure', 1);


	// INIT
		// Iniciamos el controlador de SESSIONs de PHP
		session_start();
		// Los modulos buscan estan variable, si no está no abren.
		$appcontainer = 1;
		$appcontainerfullscreen = 0;
		// Indicador del status de login...
		$loginerrorid = 0;
		$logintype = "undefined";
		// Indicador de warnings o notificaciones...
		$warningid = 0;	// SIN USO TEMPORAL
		// Indicador del status de conexión DB...
		$databaseerrorid = 0;
		$databaseerrorrefresh = 30;
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
	
	
	// INCLUDES & REQUIRES 
		include_once('includes/configuration.php');	// Archivo de configuración
		include_once('includes/functions.php');	// Librería de funciones
		include_once('includes/database.class.php');	// Class para el manejo de base de datos
		include_once('includes/databaseconnection.php');	// Conexión a base de datos

		
		if ($databaseerrorid > 0) {
			// WARNINGS & ERRORS
				//ini_set('error_reporting', E_ALL&~E_NOTICE);
				error_reporting(0);
				//ini_set('display_errors', '0');
		}
		
		
	// APP & MODULE PROCESSING
		// Procesamiento del modulo a cargar
		$module = "";
		$modulepage = "";
		$section = "";
		$action = "";
		

			// SQL Injection Check: BEGIN
				$IsSQLInjection = 0;
				$IsSQLInjection = isSQLInjection();
				if ($IsSQLInjection > 0) {
						$_GET['m'] = "page";
						$_GET['s'] = "error";
						$_GET['a'] = "view";
						unset($_POST);					
				}
			// SQL Injection Check: END


		// Si hay parametro de modulo...
		if (isset($_GET['m'])) {
			
				// Obtengo el modulo que se require
				$module = setOnlyAppParamChars($_GET['m']);
				$modulepage = $module."/".$module;
				
				// Dependiendo del módulo invocado...
				switch ($module) { 
	
					case "home": 
						$module = "home";
						$modulepage = "home";
						break; 
						
					case "logout": 
						unset($_GET['m']);
						$loginerrorid = doLogout();
						break; 
						
					default:
						if (isset($_GET['s'])) {
							$section = setOnlyAppParamChars($_GET['s']);
							$modulepage .= "_".setOnlyAppParamChars($_GET['s']);
						} 
			
						if (isset($_GET['a'])) {
							$action = setOnlyAppParamChars($_GET['a']);
							$modulepage .= "_".setOnlyAppParamChars($_GET['a']);
						} 
				} 

				// NAVIGATION page
				//$ajaxListPage = $module."/".$module."_list.php?page=";
				//$ajaxListPage = $modulepage.".php?page=";

				// Extensión final del path del script a invocar
				$modulepage .= ".php";
	

		} else {	// Si no hay parametro, activamos el default
			
				$module = "home";
				$modulepage = "home.php";
			
		}


	// SESSION & SECURITY
			$module_redirect = "";
			$modulepage_redirect = "";	
	
			// LOGIN TOOLS
			if ($module == "loginpasswordrecover") {
				require("loginformpasswordrecover.php");
				exit();
			}
			if ($module == "loginpasswordsupport") {
				require("loginformsupport.php");
				exit();
			}

		// SESSION?	
			// NO hay SESSION activa, LOGIN...
			if (!isset($_SESSION[$configuration['appkey']])) {
		
					// Tomo el vínculo hacia donde quería ir el visitante, para el eventual redirect
					$module_redirect = $module;
					$modulepage_redirect = $modulepage;	

					// COOKIE CHECK
					// Como no hay session, verificamos si hay COOKIE, siempre cuando no haya habido login submit y no es logout...
//					if ($module <> "logout" && !isset($_POST['username']) && isset($_COOKIE[$configuration['appkey']])) {
//						
//						// COOKIE SET & NOT LOGIN SUBMIT & NOT LOGOUT...
//						// Verificamos que el username o password no esten vacios...
//						if ($_COOKIE[$configuration['appkey']]['Username'] <> "a" && $_COOKIE[$configuration['appkey']]['Username'] <> "") {
//
//							// COOKIE SET & NOT LOGIN SUBMIT & NOT LOGOUT & USERNAME NOT EMPTY...
//							// Verificamos que la COOKIE no hay expirado...
//							if ($_COOKIE[$configuration['appkey']]['Expire'] > time()) {
//								// COOKIE OK, generamos el AUTOLOGIN
//								$_POST['username'] = $_COOKIE[$configuration['appkey']]['Username'];
//								$_POST['password'] = $_COOKIE[$configuration['appkey']]['Password'];
//							}
//						}
//						
//
//					}

					
					// Si hay parametros enviados desde el loginform.php
					if (isset($_POST['usernamelogin'])) {
					
						// Obtenemos las variables enviadas
						$username = setOnlyCharactersValid($_POST['usernamelogin']);
						$password = setOnlyCharactersValid($_POST['passwordlogin']);	
						
						// Hacemos el login
						$loginerrorid = doLogin($username,$password);
						
					} else {
				
						// Si no hubo datos desde loginform.php, el error es 99, no hay login para procesar...
						if ($loginerrorid == 0) { $loginerrorid = 100; }
						$logintype = "blank"; // ID login motive
						$module = "login";
						$modulepage = "loginform.php";	
				
					}
			
					// Si hubo error en el login, nos vamos al loginform.php
					if ($loginerrorid > 0) {
						
						$logintype = "loginfailed"; // ID login motive
						$sendto = "loginform.php";
						
						// Single Session Error
						if ($loginerrorid == 120) {
							$sendto = "loginformsinglesession.php";
						}
						
//							switch ($loginerrorid) {
//								case 102:
//									$sendto = "loginwarning.php";
//									break;
//								case 104: // User Blocked
//									$sendto = "loginwarning.php";
//									break;
//								default:
//									$sendto = "loginform.php";
//									break;
//							}	
							
						require($sendto);
						exit();
						
					}
					
			} else {	// SI hay SESSION activa...
				
						// Si la SESSION ya no está ACTIVA en base de datos..
						if (isSessionActive() == '0') {
							
							$loginerrorid = doLogout();
							
							$logintype = "sessioninactive"; // ID login motive
							
							$module = "login";
							$modulepage = "loginform.php";	
	
							$sendto = "loginform.php";
							require($sendto);
							exit();
							
						}
				
					// Verificamos que tenga permisos para ingresar
					$allowaccess = 1;
					$nameprivacy = 0;
					include_once('includes/securityclearance.php');	// Archivo de permisos
					if ($allowaccess == 0) {
						$modulepage = "accessdenied.php";
					}
				
			}
		
	// JUST LOGIN OR FIRST TIME
		// Verificamos si está recién loggeado [y si es su primera vez...]
		if ($_SESSION[$configuration['appkey']]['userjustlogin']) {
			$_SESSION[$configuration['appkey']]['userjustlogin'] = false;
		}
	
	// PASSWORD CHANGE REQUIRED
		// Verificamos si está obligado a cambiar de contraseña y no viene del cambio de contraseña ya aplicado...
		if ($_SESSION[$configuration['appkey']]['userpasswordchange'] == 1 && $action <> "passwordupdate") {
			
				//$_SESSION[$configuration['appkey']]['userpasswordchange'] = 0;
				
				// De donde viene...
				$module_redirect = $module;
				$modulepage_redirect = $modulepage;	
				
				// A donde lo vamos a redirigir...
				$module = "security";
				$modulepage = "security/security_users_passwordchange.php";	
			
		}

//  HTML: begin

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title><?php echo $configuration['apptitle']; ?> | <?php echo getPageTitle($module,$section,$action,''); ?></title>

	<?php if ($databaseerrorid > 0) { ?>
    <meta http-equiv="refresh" content="<?php echo $databaseerrorrefresh; ?>">
    <?php } ?>
	<meta name=viewport content="width=device-width, initial-scale=1">
    
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png" />
    
    <link href="style.css" rel="stylesheet" type="text/css" />
	<link href="dropdownstyles.css" rel="stylesheet" type="text/css" />
	<link href="jdpicker.css" rel="stylesheet" type="text/css" media="screen" />
    
   		<script type="text/javascript" src="includes/jquery.min.js"></script>
		<script type="text/javascript" src="includes/formcheck.js"></script>
		<script type="text/javascript" src="includes/jquery.tipsy.js"></script>
        <script type="text/javascript" src="includes/jquery.timeago.js"></script>
		<script type="text/javascript" src="includes/jquery.jdpicker.js"></script>
       
		<script type="text/javascript">
			function changeClass(elemento){
			   if (elemento != '' && elemento != 'home') {
					//document.getElementById(elemento).setAttribute("class", "current");
					document.getElementById('celda'+elemento).setAttribute("class", "backgroundmenu");
					document.getElementById('opcion'+elemento).setAttribute("class", "textWhite");
					var celda = document.getElementById('celda'+elemento);
					celda.className = "backgroundmenu";
					var opcion = document.getElementById('opcion'+elemento);
					opcion.className = "textWhite";
				}
			}
			
        </script>
		<script type="text/javascript" src="includes/ajaxtabs/ajaxtabs.js">
        /***********************************************
        
        * Ajax Tabs Content script v2.2- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
        
        * This notice MUST stay intact for legal use
        
        * Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
        
        ***********************************************/
        </script>
        
	<!-- NAVIGATION SCRIPTS -->
		<script type="text/javascript">
        
            // Navegación de páginas [Browsing]
            //function ajaxListPageBrowse(pagecurrent, pagenumber) {
            //      $('#ListPlaceholder').html('<p><img src="images/imageloading.gif" /></p>');
            //      $('#ListPlaceholder').load(pagecurrent+"?page="+pagenumber);
            //      //$('#example-placeholder').load("affiliation/external"+page+".htm?param="+page);
            //}

            // Navegación de páginas [Browsing]
			//	@ 20150222: Add pageparameters to function por itemtype or t parameter
            function ajaxListPageBrowse(pagecurrent, pagenumber, pageparameters) {
				
					// if NO parameters defined
						if (pagecurrent === undefined) 		{ pagecurrent = 'index.php'; } 	
						if (pagenumber === undefined) 		{ pagenumber = '1'; } 	
						if (pageparameters === undefined) 	{ pageparameters = ''; } 		
					
					// Loading ...		
					$('#ListPlaceholder').html('<p><img src="images/imageloading.gif" /></p>');
					
					// Querystring assemble...
					if(pagecurrent.indexOf('?') != -1) {
					   var pagetoload = pagecurrent+'&page='+pagenumber;
					}else{
					   var pagetoload = pagecurrent+'?page='+pagenumber;
					}
					
					if (pageparameters !== '') { pagetoload = pagetoload+'&'+pageparameters; }
					//if (pageparameters !== '') { pagetoload = pagetoload+'&t='+pageparameters; }
					
					// Load page required...
					//$('#ListPlaceholder').load(pagecurrent+"?page="+pagenumber);
					$('#ListPlaceholder').load(pagetoload);
					
            }
            
            // Navegación de páginas [Searching]
            function ajaxListPageSearch(pagecurrent, pagenumber) {
                  buscar = document.frmbusqueda.q.value;
				  if (buscar==document.frmbusqueda.q.defaultValue) { buscar = document.frmbusquedaside.q.value;	}
                  //buscar = buscar.replace(" ","+");
                  buscar = escape(buscar);
                  // Si no es una búsqueda vacía...
                  if (buscar != '') {
                      $('#ListPlaceholder').html('<p><img src="images/imageloading.gif" /></p>');
                      $('#ListPlaceholder').load(pagecurrent+"?page="+pagenumber+"&q="+buscar);
                      // Restauramos el input de búsqueda a sus valores default...
                      document.frmbusqueda.q.value = document.frmbusqueda.q.defaultValue;
                      document.frmbusquedaside.q.value = document.frmbusquedaside.q.defaultValue;
                      document.frmbusqueda.q.blur();
                  }
            }

        </script>    
        
</head>

<body onload="javascript:changeClass('<?php echo $module; ?>');if(top.location!=self.location) top.location=self.location;" id="mainbody">

<!-- ERRORS & WARNINGS: begin -->
	<?php if ($databaseerrorid > 0) { ?>
			<script>
            jQuery(document).ready(function($) {
											
				if ($('#mainbody').length > 0){
						
					//Get clicked link href
					//var image_href = $(this).attr("href");
					var image_href = 'images/lightboxmessagewarning.png';
									
					//create HTML markup for lightbox window
					var lightboxconnectionfailedinnerhtml = 
							'<img src="' + image_href +'" /><br />' +
							'<strong>Oooops!</strong>&nbsp;<br /> ' +
							'No se pueden mostrar los resultados. Perdimos conexi&oacute;n con la base de datos.<br /><br /> ' +
							'<a href="javascript:history.go(0);">Volver a intentar</a> ';
				
                
                    if ($('#lightbox').length > 0) { // #lightbox exists
                        
                        //place href as img src value
                        $('#lightboxcontent').html(lightboxconnectionfailedinnerhtml);
                        
                        //show lightbox window - you could use .show('fast') for a transition
                        $('#lightbox').show();
                    }
                    
                    else { //#lightbox does not exist - create and insert (runs 1st time only)
					
                        //create HTML markup for lightbox window
						var lightboxconnectionfaileddiv = 
						'<div id="lightbox">' +
							'<p>Click to close</p>' +
							'<div id="lightboxcontent">' + //insert clicked link's href into img src
								lightboxconnectionfailedinnerhtml +
							'</div>' +	
						'</div>';
                            
                        //insert lightbox HTML into page
                        $('body').append(lightboxconnectionfaileddiv);
                    }
          
				}
		  
				//Click anywhere on the page to get rid of lightbox window
				$('#lightbox').live('click', function() { //must use live, as the lightbox element is inserted into the DOM
					$('#lightbox').hide();
				});
           
            });
            </script>    
        <table width="100%" border="0" cellspacing="3" bgcolor="#ff0000">
        <td align="center" style="font-size:14px; color:#fff; padding: 8px 4px 8px 4px;">
            <strong>Oooops!</strong>&nbsp; 
            Perdimos conexión con la base de datos, reintentaremos en <?php echo $databaseerrorrefresh; ?> segundos!. 
            <a href="javascript:history.go(0);">Intentar ahora</a><!-- <a href="index.php">Intentar ahora</a> -->
        </td>
        </tr>
        </table>
    <?php } ?>
<!-- ERRORS & WARNINGS: end -->
    
    
<div align="center">


<!-- HEADER: begin -->
	<!-- Si hay session mostramos el menú del header -->
    <table class="headerfooter">
        <?php if (isset($_SESSION[$configuration['appkey']])) { ?>
          <tr>
           	  <td align="left">&nbsp;</td>
              <td align="right">

            <!-- HEADER NAVIGATION: begin -->
				<?php require_once('header.php') ; ?>
            <!-- HEADER NAVIGATION: end -->

              </td>
          </tr>
        <?php } else { ?>
          <tr>
            <td align="right">&nbsp;</td>
          </tr>
        <?php } ?>
    </table>
<!-- HEADER: end -->


<!-- MAIN CONTENT: begin -->
    <table class="container">

          <!-- TITLE: begin -->
          <tr>
            <td class="containertitle">
        
                    <table class="containertitlehead">
                      <tr>
                        <td valign="middle" align="left">
                        &nbsp;&nbsp;&nbsp;
                        <span style="color:#FFF; font-size:16px; font-style:bold;">
						<?php echo $configuration['instancefirstname']; ?></span><br />
                        &nbsp;&nbsp;&nbsp;
                        <span style="color:#FFF; font-size:32px; font-style:bold;">
						<?php echo $configuration['instancelastname']; ?></span>
                        </td>
                        <td>&nbsp;</td>
                        <td class="containertitleheadcelda" align="right">
                        <a href="?m=home"><img src="images/applicationlogo.png" alt="ApplicationLogo" title="Ir a Inicio" /></a>
                        &nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                    </table>
        
            </td>
          </tr>
          <!-- TITLE: end -->
      
          <!-- MENU: begin -->
            <tr>
            <td class="containermenu">

                    <table class="containermenuitems">
                      <tr class="backgroundmenuline">
                        <td><img src="images/spacer.gif" alt="" height="5px" /></td>
                      </tr>
                    </table>

            </td>
          </tr>
            <tr>
            <td class="containermenu">

                    <table class="containermenuitems">
                      <tr>
                        <td id="celdaaffiliation"><a href="?m=affiliation" id="opcionaffiliation">Afiliaci&oacute;n</a></td>
                        <td id="celdainteractions"><a href="?m=interactions" id="opcioninteractions">Interacciones</a></td>
                        <td id="celdarules"><a href="?m=rules" id="opcionrules">Reglas Negocio</a></td>
                        <td id="celdarewards"><a href="?m=rewards" id="opcionrewards">Recompensas</a></td>
                        <td id="celdareports"><a href="?m=reports" id="opcionreports">Reportes</a></td>
                        <td id="celdasecurity"><a href="?m=security" id="opcionsecurity">Seguridad</a></td>
                      </tr>
                    </table>

            </td>
          </tr>
          <!-- MENU: end -->
          
          <!-- WARNINGS & MESSAGES: begin -->
			  <?php if ($_SESSION[$configuration['appkey']]['userpasswordchange'] == 1 && $action <> "passwordupdate") { ?>
                    <tr bgcolor="#ff0000">
                    <td align="center" style="font-size:12px; color:#fff; padding: 8px 8px 8px 8px;">
                        <img src="images/security_firewall_off.ico" alt="ApplicationLogo" />&nbsp;
                        <strong>Oooops!</strong>&nbsp;Tu contrase&ntilde;a ha expirado y necesitas cambiarla!.
                    </td>
                    </tr>
              <?php } ?>

			  <?php if ($_SESSION[$configuration['appkey']]['userpasswordexpire'] < 999 && $action <> "passwordupdate") { ?>
                    <tr bgcolor="#FFF200">
                    <td align="center" style="font-size:12px; color:#000; padding: 8px 8px 8px 8px;">
                        <img src="images/security_warning.ico" alt="ApplicationLogo" />&nbsp;
                        <!--<strong>Oooops!</strong>&nbsp;Tu contrase&ntilde;a est&aacute; pr&oacute;xima a expirar!.&nbsp;-->
                        <strong>Oooops!</strong>&nbsp;Tu contrase&ntilde;a expira en 
							<?php echo $_SESSION[$configuration['appkey']]['userpasswordexpire']; ?> d&iacute;as, te sugerimos cambiarla!.&nbsp;
                        <a href="?m=security&s=users&a=passwordchange">Cambiar mi contrase&ntilde;a ahora</a>
                    </td>
                    </tr>
                    <?php $_SESSION[$configuration['appkey']]['userpasswordexpire'] = 999; ?>
              <?php } ?>
          <!-- WARNINGS & MESSAGES: end -->

          <!-- CONTENT: begin -->
          <tr>
            <td class="containercontent">
                
                <!-- CONTENIDO -->
                <!-- Si hay session mostramos el menú principal -->
                            
                <?php
				
				// Si hay módulo enviado...
                if ($modulepage <> "") {
					
					// Si el módulo armado existe... lo invocamos
					if (file_exists($modulepage)) { 
					
						setNavigationLog('navigation', 0, $modulepage);
						
						include_once($modulepage); 

					} else { // SI no existe, mandamos la página de error
					
						setNavigationLog('navigation', 1, $modulepage);
					
								//throw(new Exception('File does not exist.')); 
						
						$module = "home";
						$modulepage = "pagenotfound.php";
						//$modulepage = "accessdenied.php";
						include_once($modulepage); 
						
					} 				
                }	
                
                ?>
                <!-- CONTENIDO -->
            
            </td>
          </tr>
          <!-- CONTENT: end -->
      
    </table>
<!-- MAIN CONTENT: end -->

		<br />
        <!-- FOOTER: begin -->
            <?php require("footer.php"); ?>
        <!-- FOOTER: end -->
		<br />
        
</div>
</body>
</html>
<!-- HTML: end -->
<?php

	// DATABASE CONNECTION CLOSE
		include_once('includes/databaseconnectionrelease.php');	

?>
