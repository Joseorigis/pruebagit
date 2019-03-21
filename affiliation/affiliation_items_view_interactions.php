<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* interactions_x.php
* 	Descripción de la función.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la página es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];

// CONTAINER & IFRAME CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	// Si el llamado no viene de una página dentro del mismo dominio ...PAGE NOT FOUND
	if (!isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($appcontainer)) { 
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
		}
	} else {
		
		// INCLUDES & REQUIRES
			if (!isset($appcontainer)) {
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/functions.php');	// Librería de funciones
			}
		
		// REQUEST SOURCE VALIDATION
			$requestsource = getRequestSource();
			if ($requestsource !== 'domain' && $requestsource !== 'page') {
				$actionerrorid = 10;
				require_once('../loginwarningtab.php');
				exit();
			}

	}
	
		// Verificamos la página que se esta navegando
		if (!isset($appcontainer)) {
			
			// INIT
				// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
				include_once('../includes/functions.php');	// Librería de funciones

			// REDIRECT IF NOT IN IFRAME
				if (!isset($_GET['page'])) {
					echo '&nbsp;';
					?>
					
						<script type="text/javascript">
							<!--
							//var isInIFrame = (window.location != window.parent.location)	
							//if (!isInIFrame) { window.location = "../index.php"; }
							
							if (self == top) { window.location = "../index.php"; }
							
							-->
						</script>
					
					<?php
				}

		} 
		
		// IF NO SESSION...
		if (!isset($_SESSION[$configuration['appkey']])) {		
			require_once('../loginwarningtab.php');
			exit();
		}

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		//setNavigationLog('navigation', 0, 'affiliation/'.getCurrentPageScript());


// --------------------------------------------------
// INICIO CONTENIDO
// --------------------------------------------------


	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", getCurrentPageScript());
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.getCurrentPageScript());


	// PARAMETER VALIDATION
		// itemid... 
		if (!isset($itemid)) { $itemid = 0; }
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		
		// cardnumber
		if (!isset($cardnumber)) { $cardnumber = ''; }
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyText($_GET['cardnumber']);
			if ($cardnumber == '') { $cardnumber = '0'; }
		}

?>

            <!-- AFFILIATION ITEM INTERACTIONS -->
            <br />
            <table class="tableaffiliatedtab">
              <thead>
              <tr>
                <td colspan="4">Interacciones</td>
              </tr>
              <tr class="tableaffiliatedtabheadertr">
                <!--<td class="itemdetailheaderfirst" width="15%">Fecha</td>-->
                <td class="tableaffiliatedtabheadertd">&nbsp;&nbsp;&nbsp;<img src="images/iconinteractiondate.png" alt="Fecha" title="Fecha" /></td>
                <td class="tableaffiliatedtabheadertd">&nbsp;&nbsp;&nbsp;<img src="images/iconinteraction.png" alt="Interacción" title="Interacción" /></td>
                <td class="tableaffiliatedtabheadertd">&nbsp;&nbsp;&nbsp;<img src="images/iconinteractionreference.png" alt="Referencia" title="Referencia" /></td>
                <td class="tableaffiliatedtabheadertd">&nbsp;&nbsp;&nbsp;<img src="images/iconinteractionresults.png" alt="Resultado" title="Resultado" /></td>
              </tr>
              <thead>
              </thead>
              <tbody>
				  
			  <?php
	
// --------------------------------------------------
// INTERACTIONS SCHEDULED LIST [BEGIN]
// --------------------------------------------------

                // get items or records...
                $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemInteractions
                                     '".$itemid."', '".$cardnumber."', 'scheduled';";
                $dbconnection->query($query);
                //$items = $items + $dbconnection->count_rows(); 	// get items count
				
				// get items one by one
				while($my_row=$dbconnection->get_row()){ 
					$items = $items + 1; // items counter
					
						// row background color set
							$itemrowcolor = "fff6bf";
//							if ($items % 2 == 0) {
//								$itemrowcolor = "ffffff";
//							} else {
//								$itemrowcolor = "f9f9f9";
//							}
							
						// interactioncolor
							$interactioncolor = $my_row['InteractionColor'];
					
					?>
					<tr style="background-color:#<?php echo $itemrowcolor; ?>;font-weight:bold;">
<!-- InteractionDateTime no InteractionDate -->                        
                        <td style="border-left: 5px solid #<?php echo $interactioncolor; ?>;">
                        	<img src="images/bulletappointment.png" />&nbsp;
                        	<?php echo $my_row['InteractionDate']; ?><br />
                            	<!--<div style="padding-left:20px;">
                                <span style="font-size:8px;font-style:italic;">
                                 @ <?php echo $my_row['InteractionTime']; ?>
                                </span>
                                </div>-->
                        </td>
<!-- TYPE no TYPEDESC -->                        
                        <td>
                        	<?php echo strtoupper($my_row['InteractionType']); ?>&nbsp;<?php echo $my_row['InteractionSubType']; ?><br />
                            <a href="?m=affiliation&s=itemsphonecall&a=new&n=<?php echo $itemid; ?>&cardnumber=<?php echo $my_row['CardNumber']; ?>&interactionid=<?php echo $my_row['InteractionId']; ?>" target="_blank" title="Iniciar Llamada" >
                            <span style="font-size:10px;font-weight:bold;">
                            <?php echo $my_row['InteractionName']; ?>&nbsp;[<?php echo $my_row['InteractionId']; ?>]
                            </span></a>
                                <!--<br />
                                <span style="font-size:9px;font-style:italic;">
                                <?php echo $my_row['InteractionDescription']; ?>
                                </span>-->
                        </td>
                        <td>
                        	<?php echo $my_row['InteractionReference']; ?>
                        </td>
                        <td>
                        	<?php echo $my_row['InteractionResult']; ?>
                        </td>
					</tr>
					<?php
					}
				  
				// if NO items, set message
					if ($items > 0) {
					?>
                        <tr style="background-color:#F0F0F0;">
                        <td class="itemdetaillistelement" align="center" colspan="5">
                        <div align="center">&nbsp;</div>
                        </td>
                        </tr>
					<?php
					} 

// --------------------------------------------------
// INTERACTIONS SCHEDULED LIST [END]
// --------------------------------------------------

			  
// --------------------------------------------------
// INTERACTIONS LIST [BEGIN]
// --------------------------------------------------

                // get items or records...
                $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemInteractions
                                     '".$itemid."', '".$cardnumber."';";
                $dbconnection->query($query);
                //$items = $items + $dbconnection->count_rows(); 	// get items count
				
				// get items one by one
				while($my_row=$dbconnection->get_row()){ 
					$items = $items + 1; // items counter
					
						// row background color set
							$itemrowcolor = "ffffff";
							if ($items % 2 == 0) {
								$itemrowcolor = "ffffff";
							} else {
								$itemrowcolor = "f9f9f9";
							}
							
						// interactioncolor
							$interactioncolor = $my_row['InteractionColor'];
					
					?>
					<tr style="background-color:#<?php echo $itemrowcolor; ?>;">
<!-- InteractionDateTime no InteractionDate -->                        
                        <td style="border-left: 5px solid #<?php echo $interactioncolor; ?>;">
                        	<?php echo $my_row['InteractionDate']; ?><br />
                            	<div style="padding-left:20px;">
                                <span style="font-size:8px;font-style:italic;">
                                 @ <?php echo $my_row['InteractionTime']; ?>
                                </span>
                                </div>
                        </td>
<!-- TYPE no TYPEDESC -->                        
                        <td>
                        	<?php echo $my_row['InteractionType']; ?>&nbsp;<?php echo $my_row['InteractionSubType']; ?><br />
                            <a href="?m=affiliation&s=interactions&a=view&n=<?php echo $itemid; ?>&cardnumber=<?php echo $cardnumber; ?>&t=<?php echo $my_row['InteractionType']; ?>&q=<?php echo $my_row['RecordId']; ?>" title="Ver Detalle Interaccion">
                            <span style="font-size:10px;font-weight:bold;">
                            <?php echo $my_row['InteractionName']; ?>&nbsp;[<?php echo $my_row['InteractionId']; ?>]
                            </span></a>
                                <!--<br />
                                <span style="font-size:9px;font-style:italic;">
                                <?php echo $my_row['InteractionDescription']; ?>
                                </span>-->
                        </td>
                        <td>
                        	<?php echo $my_row['InteractionReference']; ?>
                        </td>
                        <td>
                        	<?php echo $my_row['InteractionResult']; ?>
                        </td>
					</tr>
					<?php
					}
				  
				// if NO items, set message
					if ($items == 0) {
					?>
                        <tr>
                        <td align="center" colspan="4">
                        <div align="center"><em>Sin Interacciones</em></div>
                        </td>
                        </tr>
					<?php
					} 

// --------------------------------------------------
// INTERACTIONS LIST [END]
// --------------------------------------------------
				 
              ?>
              </tbody>
            </table>
            <br />
            <table width="90%" border="0" cellspacing="3" align="center">
              <tr>
                <td align="right">
                <span style="color:#F0F0F0;font-style:italic;font-size:9px;">
                |itemid:<?php echo $itemid; ?>@<?php echo getCurrentPageScript(); ?>|
                </span>
                </td>
              </tr>
            </table>
            <br />
            <!-- AFFILIATION ITEM INTERACTIONS -->
                       
