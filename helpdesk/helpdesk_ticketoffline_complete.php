<?php
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
* 	Descripci�n de la funci�n.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la p�gina es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecuci�n
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
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}

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

		// ticket action
			$operation = "complete";

			
		// TICKETSEARCH
			$casenumber = "0";
			$casemonitor = "helpdesk@orbisfarma.com.mx";
			//$ticketid 	= '0';
			$ticketmonitor = $_SESSION[$configuration['appkey']]['email'];
			//$cardnumber = '';
			$connection  	= '';
			$store		 	= '';
			$item		 	= '';
			$invoicenumber	= '';
			$invoicedate	= '';
			$invoicemonitor	= '';
			$invoicenotes	= '';
			$ticketstatus	= '';
			
				// Obtengo el registro del ticket
					// Procesamos el registro...
					$records = 0;
					$query  = " SET ANSI_NULLS ON;SET ANSI_WARNINGS ON;";
					$query .= " EXEC dbo.usp_app_HelpDeskTicketsOfflineManage
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'".$operation."', 
											'".$actionauth."',
											'".$itemid."',
											'".$cardnumber."',
											'".$ticketmonitor."';";
					$dbtransactions->query($query);
					$records = $dbtransactions->count_rows(); 
					if ($records > 0) {
						$my_row = $dbtransactions->get_row();
						$actionerrorid = $my_row['Error']; 
						$errormessage .= $my_row['OperationMessage']; 
						
						//if ($actionerrorid == 0) {
							$ticketid 		= $my_row['RecordId']; 
							$cardnumber 	= $my_row['CardNumber']; 
							
							$connection  .= $my_row['ConnectionName'].' ['.$my_row['ConnectionId'].']';
							$store		 .= $my_row['Store'];
							$item		 .= $my_row['ItemQuantity'].'x<br />'.$my_row['ItemSKU'].'<br />';
							$item		 .= '<span style="font-size:12px;">';
							$item		 .= $my_row['ItemName'].'<br />['.$my_row['ItemBrand'].']';
							$item		 .= '</span>';

							$invoicenumber 	.= $my_row['InvoiceNumber'];
							$invoicedate 	.= $my_row['InvoiceDate'];
							$invoicemonitor	.= $my_row['TicketOfflineMonitor'];
							$invoicenotes	.= $my_row['TicketOfflineNotes'];
							
							$ticketstatus	.= $my_row['TicketOfflineStatus'];

							$casenumber  = $my_row['CaseNumber']; 
							$casemonitor = $my_row['CaseMonitor']; 

						//}
			
						// SEND NOTIFY
						if ($actionerrorid == 0) { 
							$TicketContent = $my_row;
							$TicketAction = "";
							$TicketAction = $operation;
							require_once('includes/HelpDeskTicketOfflineSend.php');
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
                  <tr>
                    <td>
                    Tarjeta<br />
                    <span class="textMedium"><em><?php echo $cardnumber; ?></em></span><br />
                    <br />
                    Art&iacute;culo<br />
                    <span class="textMedium"><em><?php echo $item; ?></em></span><br />
                    <br />
                    </td>
                  </tr>                
                
						<?php 
                        // Si el usuario fue eliminado con exito....
                        if ($actionerrorid == 0) { 
                        ?>
                              <tr>
                                <td>
            
                                    <img src="images/iconresultok.png" /><br /><br />
                                    El TICKET OFFLINE ha sido CERRADO!.<br />
                                    <br />
                                    <br />
                                    Ticket Offline N&uacute;mero: 
                                    <span style="font-size:12px; font-weight:bold;"><?php echo $casenumber; ?></span>
                                    <br />
                                    <br />
                                    <span style="font-style:italic;">
                                    El ticket ha sido enviado al usuario que lo solicit&oacute;.
                                    </span>
            
                                </td>
                              </tr>                          

                        <?php } else { ?>	
                              
                          <tr>
                            <td>
                            
                                <img src="images/iconresultwrong.png" /><br />
                                <br /><br />
                                El TICKET OFFLINE NO pudo ser CERRADO!.<br />
                                <br />
                                <br />
                                Ticket Offline N&uacute;mero: <?php echo $casenumber; ?>
                                <br />
                                <br />
                                <?php
									// Error message...
									switch ($actionerrorid) {
										case 1:
											echo "La informaci&oacute;n ingresada est&aacute; incompleta.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 2:
											echo "La informaci&oacute;n ingresada es incorrecta.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										case 925:
											echo "El ticket no fue encontrado para actualizar.<br />";
											echo "Por favor, verifique la informaci&oacute;n e intente de nuevo.<br />";
											break;
										default:
											echo "Ocurri&oacute; un error con el procesamiento del registro.<br />";
											echo "Por favor, intente m&aacute;s tarde.<br />";
									}
								
								?>	
                                <span style="font-style:italic;">
									<?php 
                                    if (isset($errormessage)) {
                                            if ($errormessage !== "") {
                                                echo "<br />";
                                                echo $errormessage;
                                            }
                                    }
                                    ?>
                                </span>
                                <br />
                                <span style="font-style:italic;font-size:11px;color:#ADB1BD;">
								<?php echo $actionauth; ?> [Err <?php echo $actionerrorid; ?>]
                                </span>
                                <br />

                            </td>
                          </tr>     
                                                        
                        <?php } ?>	
                        
                     </table>
  
                        <br /><br />
                        <table class="botones2">
                          <tr>
                            <td class="botonstandard">
                            <img src="images/bulletinvoice.png" />&nbsp;
                            <a href="?m=helpdesk&s=ticketoffline&a=view&cardnumber=<?php echo $cardnumber; ?>&n=<?php echo $itemid; ?>">Ver Ticket</a>
                            </td>
                          </tr>
                        </table>
                    	<br /><br />
                
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

