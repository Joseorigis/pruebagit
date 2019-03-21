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
		$itemfile = strtolower($itemfile);


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


	// --------------------------------------------------------------------------------
	// ORBIS reports: begin
	//		Reportes predefinidos en Orbis [NO MODIFICAR]
	// --------------------------------------------------------------------------------

			// ONLY @ orbisorvee
			if ($configuration['appkey'] == "orbisportalmain") {

				// ONLY admins ...
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { 

					// RULESWARNINGS
							if ($itemtype == "ruleswarningslog") {

								$ReportLink  = "reports/ReportsRulesWarningsDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";

							}	// [if ($itemtype == "settlementindex") {]
						
					// SETTLEMENT
							if ($itemtype == "settlementindex") {

								$ReportLink  = "reports/ReportsHelpDeskSettlementIndexDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";

							}	// [if ($itemtype == "settlementindex") {]

					// BILLING
							if ($itemtype == "billingdetail") {

								$ReportLink  = "reports/ReportsHelpDeskBillingDetailDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";

							}	// [if ($itemtype == "billingdetail") {]

					// TICKETS OFFLINE
							if ($itemtype == "ticketsoffline") {

								$ReportLink  = "reports/ReportsHelpDeskTicketsOfflineDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";

							}	// [if ($itemtype == "ticketsoffline") {]

						}	// [if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 || $_SESSION[$configuration['appkey']]['userprofileid'] == 2) {]
				
			}	// [if ($configuration['appkey'] == "orbisportalmain") {]

	// --------------------------------------------------------------------------------
	// ORBIS reports: end
	// --------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------
	// ORVEE reports: begin
	//		Reportes predefinidos en Orvee [NO MODIFICAR]
	// --------------------------------------------------------------------------------
		
				// ONLY admins ...
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { 

					// AFFILIATION LISTS
							if ($itemtype == "list") {

								$ReportLink  = "reports/ReportsAffiliationListsDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								//echo $ReportLink;
								//header("Refresh: 0;url=$ReportLink");
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
								//exit();

							} // [if ($itemtype == "list") ]

					// SECURITY USERS
							if ($itemtype == "securityusers") {

								$ReportLink  = "reports/ReportsSecurityUsersDownload.php";
								$ReportLink .= '?'.$QueryStringHeader;
								//echo $ReportLink;
								//header("Refresh: 0;url=$ReportLink");
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
								//exit();

							} // [if ($itemtype == "securityusers")]
						
						}	// [if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 || $_SESSION[$configuration['appkey']]['userprofileid'] == 2) {]

	// --------------------------------------------------------------------------------
	// ORVEE reports: end
	// --------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------
	// FILES DOWNLOAD reports: begin
	//		Reportes descargables generados en algún folder, el archivo a descargar ya existe en algún folder y fue generado por otro proceso.
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
								$ReportLink .= '?'.$itemfile;
								
								//echo $ReportLink;
								//header("Refresh: 0;url=$ReportLink");
								echo "<meta http-equiv='refresh' content='0; URL=".$ReportLink."' />";
								//exit();

							} // [if ($itemtype == "file") {]


	// --------------------------------------------------------------------------------
	// FILES DOWNLOAD reports: end
	// --------------------------------------------------------------------------------


	// --------------------------------------------------------------------------------
	// INSTANCE reports: begin
	//		Reportes adhoc de la personalización de la instancia [EDITABLE]
	// --------------------------------------------------------------------------------

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
					if ($ReportLink !== "") {
				?>

						<br />
						Se reporte se est&aacute; procesando...
						<br />
						<br />
						<br />
						Si no comienza la descarga en unos momentos, haga <span style="font-weight:bold"><a href="<?php echo $ReportLink; ?>">click aqu&iacute;.</a></span>
						<br />
						<span style="font-style:italic;font-size:8px;color:#f0f0f0;">
						[<?php echo $QueryStringHeader; ?>]
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
						[<?php echo $QueryStringHeader; ?>]
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

