<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
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

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
		// ERROR MESSAGE
		$errormessage = "";


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}

	// PARAMETER VALIDATION
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}
			
			$cardnumber = "";
			if (isset($_GET['cardnumber'])) {
				$cardnumber = setOnlyText($_GET['cardnumber']);
			}
	
		// itemtype
			$itemtype = 'ticketoffline';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'ticketoffline'; }
			}
			$itemtype = strtolower($itemtype);

		// ticket params
			$ticketid = 0;
			if (isset($_GET['n'])) {
				$ticketid = setOnlyNumbers($_GET['n']);
				if ($ticketid == '') { $ticketid = 0; }
				if (!is_numeric($ticketid)) { $ticketid = 0; }
			}
			
		// TICKETSEARCH
			$casenumber = "0";
			$casemonitor = "helpdesk@orbisfarma.com.mx";
			//$ticketid 	= '0';
			//$cardnumber = '';
			$connection  	= '';
			$connectionkey 	= '';
			$store		 	= '';
			$item		 	= '';
			$invoicenumber	= '';
			$invoicefile	= '';
			$invoicedate	= '';
			$invoicemonitor	= '';
			$invoicenotes	= '';
			$ticketstatus	= '';
			$ticketwarnings = '';
			
				// Obtengo el registro del ticket
					// Procesamos el registro...
					$records = 0;
					$query  = " SET ANSI_NULLS ON;SET ANSI_WARNINGS ON;";
					$query .= " EXEC dbo.usp_app_HelpDeskTicketsOfflineManage
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'view', 
											'".$actionauth."',
											'".$itemid."',
											'".$cardnumber."';";
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row = $dbtransactions->get_row();
						$actionerrorid = $my_row['Error']; 
						$errormessage .= $my_row['OperationMessage']; 
						$ticketwarnings = trim($my_row['OperationMessage']); 
						
						if ($actionerrorid == 0) {
							$ticketid 		= $my_row['RecordId']; 
							$cardnumber 	= $my_row['CardNumber']; 
							
							$connection  .= $my_row['ConnectionName'].' ['.$my_row['ConnectionId'].']';
							$connectionkey .= $my_row['ConnectionKey'];
							$store		 .= $my_row['Store'];
							$item		 .= $my_row['ItemQuantity'].'x<br />'.$my_row['ItemSKU'].'<br />';
							$item		 .= '<span style="font-size:12px;">';
							$item		 .= $my_row['ItemName'].'<br />['.$my_row['ItemBrand'].']';
							$item		 .= '</span>';

							$invoicenumber 	= $my_row['InvoiceNumber'];
							$invoicedate 	= $my_row['InvoiceDate'];
							$invoicefile	= $my_row['TicketOfflineFile'];
							$invoicemonitor	= $my_row['TicketOfflineMonitor'];
							$invoicenotes	= $my_row['TicketOfflineNotes'];
							
							$ticketstatus	= $my_row['TicketOfflineStatus'];

							$casenumber  = $my_row['CaseNumber']; 
							$casemonitor = $my_row['CaseMonitor']; 

						}
			

					} else {
						$actionerrorid = 99;
					}


	
	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

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
                
                <?php if ($actionerrorid == 99) { ?>

                        <table class="tablemessage">
                          <tr>
                            <td bgcolor="#FF0000">&nbsp;</td>
                            <td bgcolor="#F0F0F0">			
                                    <br />
                                    <img src="images/security_firewall_off.png" alt="Error" />
                                    <br />
                                    <br />
                                    <span class="textMedium">Oooops!
                                    <br />
                                    El elemento no fue encontrado!.</span>
                                    <br />
                                    <br />
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    Por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                    <br />
                                    <br />
        
                            </td>
                          </tr>
                        </table>
                                        
                <?php } else { ?>

                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagesettings.png" alt="Help Desk" title="Help Desk" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Help Desk<br />
                                Ticket Offline
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  
                  <?php if ($ticketwarnings !== "") { ?>
                      <tr>
                        <td style="background-color:#FFCFCA; border-left:4px solid #e74c3c;">
                        <span style="font-style:italic;"> 
                        <span style="font-weight:bold;"> 
                        Atenci&oacute;n:
                        </span>
                        <?php echo str_replace("|","<br />&middot;&nbsp;",$ticketwarnings); ?><br />
                        </span><br />
                        </td>
                      </tr>
                  <?php } ?>
                  
                  <tr>
                    <td>
                    Ticket Offline<br />
                    <span class="textMedium"> 
                    <?php echo $casenumber; ?><br />
                    <?php echo $ticketstatus; ?>
                    </span><br />
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Tarjeta<br />
                    <span class="textMedium"> 
                    <?php echo $cardnumber; ?>
                    </span><br />
                    </td>
                  </tr>
 
                  <tr>
                    <td>
                    Cadena<br />
                    <span class="textMedium"> 
                    <?php echo $connection; ?>
                    </span><br />
				<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						  $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                     <span style="color:#CCCCCC;font-size:10px;">
                    <?php echo $connectionkey; ?>
                    </span>
				<?php } ?>
                   </td>
                  </tr>
 
                   <tr>
                    <td>
                      Sucursal<br/>
                    <span class="textMedium"> 
                    <?php echo $store; ?>
                    </span><br />
                    </td>
                  </tr>

                  <tr>
                    <td>
                     Ticket<br/>
                    <span class="textMedium"> 
                    <?php echo $invoicenumber; ?>
                    @ <?php echo $invoicedate; ?>
                    </span><br />
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Art&iacute;culo<br />
                    <span class="textMedium"> 
                    <?php echo $item; ?>
                    </span><br />
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Archivo<br />
                    <?php if ($invoicefile == '') { ?>
                     <a href="https://storage.orveecrm.com/filemanager/index.php?n=<?php echo $itemid; ?>&t=ticketoffline" target="_blank" title="Agregar Archivo">
					<img src="https://storage.orveecrm.com/filemanager/imageoff.png" /></a>
                    <?php } else { ?>
                     <a href="<?php echo $my_row['TicketOfflineFile']; ?>" target="_blank" title="Ver Archivo">
					<img src="https://storage.orveecrm.com/filemanager/imageicon.png" /></a>
                   <?php } ?>
                    <br />
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Monitor<br />
                    <span class="textMedium"> 
                    <?php echo $invoicemonitor; ?>
                    </span><br />
                    </td>
                  </tr>

                   <tr>
                    <td>
                    Notas<br />
                    <span class="textSmall"> 
                    <?php echo $invoicenotes; ?>
                    </span><br />
                    </td>
                  </tr>

                </table>
   
					<?php if ($ticketstatus == "NEW") { ?>
                        <br /><br />
                        <table class="botones2">
                          <tr>
                             <td class="botonstandard">
                            <img src="images/bulletcancel.png" />&nbsp;
                            <a href="?m=helpdesk&s=ticketoffline&a=reject&cardnumber=<?php echo $cardnumber; ?>&q=<?php echo $itemid; ?>&n=<?php echo $itemid; ?>">Rechazar</a>
                            </td>
                           <td class="botonstandard">
                            <img src="images/bulletcheck.png" />&nbsp;
                            <a href="?m=helpdesk&s=ticketoffline&a=activate&cardnumber=<?php echo $cardnumber; ?>&q=<?php echo $itemid; ?>&n=<?php echo $itemid; ?>">Autorizar</a>
                            </td>
                          </tr>
                        </table>
					<?php } ?>

				<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
						  $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>

					<?php if ($ticketstatus == "AUTHORIZED") { ?>
                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulleton.png" />&nbsp;
                            <a href="?m=helpdesk&s=ticketoffline&a=complete&cardnumber=<?php echo $cardnumber; ?>&q=<?php echo $itemid; ?>&n=<?php echo $itemid; ?>">Finalizar</a>
                            </td>
                          </tr>
                        </table>
					<?php } ?>
                    
				<?php } ?>

                <?php } ?>
                    
                    	<br /><br />
   
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
                    <table class="modulesectiontitlesmall">
                        <tr>
                        <td>Acciones Afiliado</td>
                        </tr>
                    </table>
                    <br />
                    <table class="sidebar">
                        <tr>
                        <td>
                        <img src="images/bulletaffiliated.png" />&nbsp;<a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumber; ?>"  target="_blank">Ver Tarjeta</a><br />
                        <img src="images/bulletlist2.png" />&nbsp;<a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid=<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>&actionauth=0&cardnumber=<?php echo $cardnumber; ?>" target="_blank">Ver Historial</a>
                        </td>
                        </tr>
                    </table>
                    <br /><br />                    
        
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

