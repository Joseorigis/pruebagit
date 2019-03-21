<?php 
/**
*
* reports_items_download.php
*
* Hub o controlador de reportes en descargable.
*	Hay una sección de keywords reservados o reportes estandar de la plataforma, 
*		& otra sección para las excepciones o personalización
*	+ Modificaciones 20180211. raulbg. Se implementa el descargado de archivos.
*	+ Modificaciones 20170921. raulbg. Se agrega SecurityUsers.
*	+ Modificaciones 20170914. raulbg. Implementación Inicial.
*
* @version 		20180211.orvee
* @category 	reports
* @package 		orvee
* @author 		raulbg <raulbg@origis.com>
* @deprecated 	20170921.orvee
* @deprecated 	20170914.orvee
*
*/

// HEADERS
	// Verificamos si la página es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
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
	

// CONTAINER CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	if (!isset($appcontainer)) {
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'none';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'none'; }
		}
		$itemtype = strtolower($itemtype);

		$itemquery = '';
		if (isset($_GET['q'])) {
			$itemfile = setOnlyCharactersValid($_GET['q']);
			if ($itemquery == '') { $itemquery = 'none'; }
		}
		$itemquery = strtolower($itemquery);

		$itemfile = '';
		if (isset($_GET['f'])) {
			$itemfile = setOnlyCharactersValid($_GET['f']);
		}


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }


	// QUERYSTRING
		$QueryStringHeader = "";
		if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = urldecode($_SERVER['QUERY_STRING']); }
			// Si hay comillas, redirigimos la ejecución
			if (strpos($QueryStringHeader, "'") !== false) { 
				$actionerrorid = 66;
				$QueryStringHeader = str_replace("'", '', $QueryStringHeader);
			}
		
		
	// ITEMTYPE selector...
	// index.php?m=reports&s=items&a=download&t=keyword&f=file
		$ReportLink 	= "";
		$ReportFound	= 66;


	// --------------------------------------------------------------------------------
	// ORBIS reports: begin
	//		Reportes predefinidos en Orbis [NO MODIFICAR]
	// --------------------------------------------------------------------------------

			// ONLY @ orbisorvee
			if ($configuration['appkey'] == "orbisportalmain") {

				// ONLY admins ...
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { 

					// SETTLEMENT
							if ($itemtype == "settlementindex") {

								$ReportLink  = "reports/ReportsHelpDeskSettlementIndexDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}

							}	// [if ($itemtype == "settlementindex") {]

					// BILLING
							if ($itemtype == "billingdetail") {

								$ReportLink  = "reports/ReportsHelpDeskBillingDetailDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}

							}	// [if ($itemtype == "billingdetail") {]


						}	// [if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 || $_SESSION[$configuration['appkey']]['userprofileid'] == 2) {]
				
			}	// [if ($configuration['appkey'] == "orbisportalmain") {]


			// ONLY @ orbisorvee
					// RULESWARNINGS
							if ($itemtype == "ruleswarningslog") {

								$ReportLink  = "reports/ReportsRulesWarningsDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}
								
							}	// [if ($itemtype == "settlementindex") {]
						

					// TICKETS OFFLINE
							if ($itemtype == "ticketsoffline") {

								$ReportLink  = "reports/ReportsHelpDeskTicketsOfflineDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}
								
							}	// [if ($itemtype == "ticketsoffline") {]


	// --------------------------------------------------------------------------------
	// ORBIS reports: end
	// --------------------------------------------------------------------------------



	// --------------------------------------------------------------------------------
	// ORVEE reports: begin
	//		Reportes predefinidos en Orvee [NO MODIFICAR]
	// --------------------------------------------------------------------------------
		
			// ------------------------------
			// ADMINS reports
			// ------------------------------
				// ONLY admins ...
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { 

					// AFFILIATION LISTS
							if ($itemtype == "list") {

								$ReportLink  = "reports/ReportsAffiliationListsDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}


							} // [if ($itemtype == "list") ]

					// SECURITY USERS
							if ($itemtype == "securityusers") {

								$ReportLink  = "reports/ReportsSecurityUsersDownload.php";
								
								// If report exists...
								if (file_exists($ReportLink)) {
									$ReportFound = 1;
									// Paste querystring
									$ReportLink .= '?'.$QueryStringHeader;
									// Redirect to file
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
									//exit();
								} else {
									$ReportFound = 0;
								}


							} // [if ($itemtype == "securityusers")]
						
						}	// [if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 || $_SESSION[$configuration['appkey']]['userprofileid'] == 2) {]


			// ------------------------------
			// EVENTOS ADVERSOS (FARMACOVIGILANCIA & QUEJAPRODUCTO)
			// ------------------------------
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						$_SESSION[$configuration['appkey']]['userprofileid'] == 2 || 
						$_SESSION[$configuration['appkey']]['userprofileid'] == 9) { 

							if ($itemtype == "quejaproducto") {

											$ReportLink  = "reports/ReportsEAQuejaProductoDownload.php";

											// If report exists...
											if (file_exists($ReportLink)) {
												$ReportFound = 1;
												// Paste querystring
												$ReportLink .= '?'.$QueryStringHeader;
												// Redirect to file
												echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
												//exit();
											} else {
												$ReportFound = 0;
											}


							} // [if ($itemtype == "quejaproducto") {]

							if ($itemtype == "farmacovigilancia") {

											$ReportLink  = "reports/ReportsEAFarmacovigilanciaDownload.php";

											// If report exists...
											if (file_exists($ReportLink)) {
												$ReportFound = 1;
												// Paste querystring
												$ReportLink .= '?'.$QueryStringHeader;
												// Redirect to file
												echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
												//exit();
											} else {
												$ReportFound = 0;
											}


							}

						} // [if ($itemtype == "farmacovigilancia") {]

	// --------------------------------------------------------------------------------
	// ORVEE reports: end
	// --------------------------------------------------------------------------------



	// --------------------------------------------------------------------------------
	// FILES DOWNLOAD reports: begin
	//		Reportes descargables generados en algún folder, el archivo a descargar ya existe en algún folder y fue generado por otro proceso.
	//		[NO MODIFICAR]
	// --------------------------------------------------------------------------------

					// FILES
							if ($itemtype == "file") {

								// Default folder ... for local
								$ReportLink  = "reports/";
								
								if ($itemquery !== "local") {
									// Get folder for files at parameters
									$items = 0;
									$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
														'0', 
														'".$configuration['appkey']."', 
														'view', 
														'crm', 
														'0', 
														'Reports', 
														'StorageFolder';";
									$dbconnection->query($query);
									$items = $dbconnection->count_rows();
									if ($items > 0) {
											$my_row=$dbconnection->get_row();
											$ReportLink = trim($my_row['ParameterValue']);
									} // [if ($items > 0) {]
								
									if ($ReportLink == "") {
										$ReportLink  = "reports/";
									} // [if ($ReportLink == "")]
									
								} // [if ($itemquery !== "local") {]
								
								// Set final path for report
								$ReportLink .= $itemfile;
								
								// If file exists at folder or URL...
								if (url_exists($ReportLink) || file_exists($ReportLink)) {
									$ReportFound = 1;
									//echo $ReportLink;
									//header("Refresh: 0;url=$ReportLink");
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
								} else {
									$ReportFound = 0;
								}

							} // [if ($itemtype == "file") {]


	// --------------------------------------------------------------------------------
	// FILES DOWNLOAD reports: end
	// --------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------
	// LINK reports: begin
	//		Reportes que te redirigen a una liga o reporte en web ya existente.
	//		[NO MODIFICAR]
	// --------------------------------------------------------------------------------

					// FILES
							if ($itemtype == "link") {

								// Default folder ... for local
								$ReportLink  = "";
								
								// Set final path for report
								$ReportLink .= $itemfile;
								
								// If file exists at folder or URL...
								if (url_exists($ReportLink) || file_exists($ReportLink)) {
									$ReportFound = 1;
									//echo $ReportLink;
									//header("Refresh: 0;url=$ReportLink");
									echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
								} else {
									$ReportFound = 0;
								}

							} // [if ($itemtype == "file") {]


	// --------------------------------------------------------------------------------
	// LINK reports: end
	// --------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------
	// INSTANCE reports: begin
	//		Reportes adhoc de la personalización de la instancia [EDITABLE]
	// --------------------------------------------------------------------------------

			// EDITAR en PHP incluido...
			include_once('reports/ReportsInstance.php');

	// --------------------------------------------------------------------------------
	// INSTANCE reports: end
	// --------------------------------------------------------------------------------



?>
               

<!-- MODULO: begin -->
<table class="template">
  <tr>
  	<td>

        <!-- MODULO HEADER:begin -->
			<?php require_once('headertitle.php') ; ?>
        <!-- MODULO HEADER:end -->

    <!-- MODULO CONTENIDO: begin -->
    <table class="template">
      <tr>


		    <!-- MODULO BODY: begin -->
        <td class="templatemainbody">
        <br />

				<?php 
					if ($ReportLink !== "" && $ReportFound == 1) {
				?>

						<br />
						Se reporte se est&aacute; procesando...
						<br />
						<br />
						<br />
						Si no comienza la descarga en unos momentos, haga <span style="font-weight:bold"><a href="<?php echo $ReportLink; ?>">click aqu&iacute;.</a></span>
						<br />
						<span style="font-style:italic;font-size:8px;color:#f0f0f0;">
						[<?php echo $QueryStringHeader; ?>]<br />[<?php echo $ReportLink; ?>]
						</span>
						<br />
						                    
				<?php 
					} else {
				?>

						<br />
						<br />
						<img src="images/iconresultwrong.png" /><br />
						<br /><br />
						El reporte no pudo ser procesado!<br />
						<br />
						<br />
						<span style="font-style:italic;font-size:8px;color:#f0f0f0;">
						[<?php echo $QueryStringHeader; ?>]<br />[<?php echo $ReportLink; ?>]
						</span>
						<br />
                                
				<?php 
					}
				?>

			<br />
			<br />
        
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
					<!-- Incluimos el sidebar del modulo-->
                    <?php 
					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = $module."_sidebar.php";
					include($sidebarfile);
					?>
            
        </td>
            <!-- MODULO TOOLBAR: end -->

      </tr>
    </table>
    <!-- MODULO CONTENIDO: end -->

    
	<br />
	</td>
  </tr>
</table>
<!-- MODULO: end -->

<?php 
//$url = "http://ejemplo.com/una-url-a-comprobar";
//$urlexists = url_exists( $url );
// https://cybmeta.com/comprobar-en-php-si-existe-un-archivo-o-una-url

function url_exists( $url = NULL ) {

    if( empty( $url ) ){
        return false;
    }

    // get_headers() realiza una petición GET por defecto
    // cambiar el método predeterminadao a HEAD
    // Ver http://php.net/manual/es/function.get-headers.php
    stream_context_set_default(
        array(
            'http' => array(
                'method' => 'HEAD'
             )
        )
    );
    $headers = @get_headers( $url );
    sscanf( $headers[0], 'HTTP/%*d.%*d %d', $httpcode );

    //Aceptar solo respuesta 200 (Ok), 301 (redirección permanente) o 302 (redirección temporal)
    $accepted_response = array( 200, 301, 302 );
    if( in_array( $httpcode, $accepted_response ) ) {
        return true;
    } else {
        return false;
    }
}

?>