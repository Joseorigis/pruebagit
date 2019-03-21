<?php 
/**
*
* reports_transactions_view.php
*
* Mostrar el detalle de una transacción.
*	+ Modificaciones 20170928. raulbg. Se agrego vinculo a consulta de regla de negocio.
*	+ Modificaciones 20170913. raulbg. Implementación Inicial.
*
* @version 		20170928.orvee
* @category 	reports
* @package 		orvee
* @author 		raulbg <raulbg@origis.com>
* @deprecated 	20170913.orvee
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


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['q'])) {
			$itemid = setOnlyNumbers($_GET['q']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'transaction';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'transaction'; }
		}
		$itemtype = strtolower($itemtype);
		// CardNumber
		$cardnumber = '';
	
	
	// AFFILIATIONID
			// Obtenemos el ID de la afiliación
			$affiliationid = 0;
			if (isset($_GET['n'])) {
				$affiliationid = setOnlyNumbers($_GET['n']);
				if ($affiliationid == '') { $affiliationid = 0; }
				if (!is_numeric($affiliationid)) { $affiliationid = 0; }
			}

					// Consultamos al afiliado...
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
											'".$affiliationid."', '0';";
					$dbconnection->query($query);
					$records = $dbconnection->count_rows(); 
					$my_row=$dbconnection->get_row();
					
					$cardnumber = '';
						if (isset($my_row['Tarjeta'])) {
							$cardnumber = $my_row['Tarjeta']; 
						}
						if (isset($my_row['CardNumber'])) {
							$cardnumber = $my_row['CardNumber']; 
						}
					$affiliationname = '';
						if (isset($my_row['Nombre'])) {
							$affiliationname = $my_row['Nombre']; 
						}
						if (isset($my_row['CardName'])) {
							$affiliationname = $my_row['CardName']; 
						}
					//$actionerrorid 		= $my_row['Error']; 
					$actionerrorid 		= 0; 
	

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
                                <img src="images/imageuser.gif" alt="Affiliated Status" title="Affiliated Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                <?php echo $cardnumber; ?><br />
                                <!--<span class="textSmall"><?php echo $affiliationname; ?><br /></span>-->
                                Transacci&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
				</table>
                <br />
				<br />
                    
                    <?php
						$EsTicketOffline = 0;
					
						$items = 0;
						$query = " EXEC dbo.usp_app_ReportsTransactions 
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',						
											'".$itemtype."', '".$itemid."', '".$configuration['appkey']."';";
						$dbtransactions->query($query);
						$items = $dbtransactions->count_rows();
						
						if ($items == 0) {
							?>
                            
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
                                                La transacci&oacute;n no fue encontrada!.</span>
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                                <br />
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                                                <br />
                    
                                        </td>
                                      </tr>
                                    </table>
                            
                            <?php
						} else {
						
							
								$my_row = $dbtransactions->get_row();
								$saleid = $my_row['SaleTransactionId'];
								$itemid = $my_row['CardAffiliationId']; // Reasignamos el ItemId
								$cardnumber = $my_row['CardNumber']; // Reasignamos el cardnumber

									// Para mostrar ticket...
									if ($my_row['InvoiceFile'] !== '') 
										{ $EsTicketOffline = 1; }
							
							?>
							
										<!-- LIST GRID:begin -->          
											<table width="50%">
											<tr>
											<td>
											  
											<table class="tablelistitems">
											  <thead>
											  <tr>
												<td colspan="2">Transacci&oacute;n</td>
											  </tr>
											  </thead>
											  <tbody>
											  <tr>
												<td width="40%">Transacci&oacute;n</td>
												<td width="60%"><strong><?php echo $my_row['TransactionType']; ?></strong>&nbsp <i>[<?php echo $my_row['TransactionHubData']; ?>]</i></td>
											  </tr>	                                      
											  <tr>
												<td>No. Transacci&oacute;n</td>
												<td><strong><?php echo $my_row['TransactionNo']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Autorizaci&oacute;n</td>
												<td><strong><?php echo $my_row['SaleAuthNumber']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>No. Ticket</td>
												<td>
                                                	<strong><?php echo $my_row['InvoiceNumber']; ?></strong>
                                                    <?php if ($EsTicketOffline == 1) { ?>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="<?php echo $my_row['InvoiceFile']; ?>" target="_blank" title="Ver Ticket">
                                                    <img src="images/bulletinvoice.png" alt="Ver Script" title="Ver Script" />
                                                    </a>
                                                    <?php } ?>
                                                </td>
											  </tr>	                                      
											  <tr>
												<td>Tarjeta</td>
												<td><strong><?php echo $my_row['CardNumber']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Sucursal</td>
												<td><strong><?php echo $my_row['StoreBrand']; ?></strong><br />
                                                <span style="font-size:10px;font-style:italic;">
                                                @ <?php echo $my_row['StoreName']; ?> [<?php echo $my_row['StoreId']; ?>]<br />
                                                <span style="font-size:8px;">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <?php echo $my_row['StoreCounty']; ?>, <?php echo $my_row['StoreState']; ?> <?php echo $my_row['StoreZipCode']; ?>
                                                </span>
                                                </span>
                                                </td>
											  </tr>	                                      
											  <tr>
												<td>Empleado</td>
												<td><strong><?php echo $my_row['SaleTransactionEmployee']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Caja</td>
												<td><strong><?php echo $my_row['SaleTransactionPOS']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Entrega</td>
												<td><strong><?php echo $my_row['SaleDeliveryPlace']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Fecha</td>
												<td><strong><?php echo $my_row['TransactionDate']; ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Importe</td>
												<td><strong>$ <?php echo round($my_row['InvoiceAmount'],2); ?></strong></td>
											  </tr>	                                      
											  <tr>
												<td>Abono</td>
												<td><strong><?php echo round($my_row['PointsEarned'],2); ?> pts</strong></td>
											  </tr>	                                      
											  <tr>
												<td>Redenci&oacute;n</td>
												<td><strong><?php echo round($my_row['PointsRedeemed'],2); ?> pts</strong></td>
											  </tr>	                                      
											  </tbody>
											  </table>
											  <br />
											  </td>
											  </tr>
											  </table>
											  
												<?php
												
													$query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_ReportsTransactions 
																		'".$_SESSION[$configuration['appkey']]['userid']."', 
																		'".$configuration['appkey']."',													
																		'saleitems', '".$saleid."', '".$configuration['appkey']."';";
													$dbtransactions->query($query);
												
												?>
											<table width="100%">
											<tr>
											<td>
											  
											<table class="tablelistitems">
											  <thead>
											  <tr>
												<td colspan="8">Transacci&oacute;n Art&iacute;culos</td>
											  </tr>
											  <tr>
												<td align="left">Art&iacute;culo</td>
												<td align="left">Marca</td>
												<td align="right">Cantidad</td>
												<td align="right">Importe</td>
												<td align="right">Puntos</td>
												<td align="right">Descuento</td>
												<td align="right">Bonificaci&oacute;n</td>
												<td align="center">&nbsp;</td>
											  </tr>
											  </thead>
											  <tbody>
											<?php
											$itemsku = '0';
											while($my_row=$dbtransactions->get_row()){
														$itemsku = trim($my_row['Item']);
													  ?>
													  <tr>
														<td align="left">
														<a href="?m=rules&s=item&a=check&n=<?php echo $my_row['Item']; ?>&connection=<?php echo $my_row['ConnectionId']; ?>&cardnumber=<?php echo $cardnumber; ?>" target="_blank" title="Ver Regla Negocio" >
														<em><?php echo $my_row['Item']; ?></em>
														</a><br />
														<?php echo $my_row['ItemName']; ?>
														</td>
														<td align="left">
														<?php echo $my_row['ItemBrand']; ?>
														</td>
														<td align="right">
														<?php echo $my_row['Quantity']; ?>
														</td>
														<td align="right">
														$ <?php echo round($my_row['Amount'],2); ?>
														</td>
														<td align="right">
														<?php echo round($my_row['Points'],2); ?>
														</td>
														<td align="right">
														<?php echo $my_row['Discount']; ?> %
														</td>
														<td align="right">
														<?php echo $my_row['Bonus']; ?>
														</td>
														<td align="right">
														<?php if ($my_row['Bonus'] == '1') { ?>

							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                    <a href="?m=helpdesk&s=bonusrecord&a=reject&t=bonusrecord&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>&q=<?php echo $saleid; ?>&itemsku=<?php echo $itemsku; ?>&actionauth=<?php echo $actionauth; ?>"  onclick="return confirm('La Bonificación será RECHAZADA. Esta acción no puede deshacerse. Confirmas que deseas rechazarla?')">
                                    <img src="images/bulletremove.png" alt="Rechazar Bonificación" />&nbsp;Rechazar
                                    </a>
                            <?php } ?>
                                                        
                                                        <?php } else { ?>
                                                        &nbsp;
                                                        <?php } ?>
														</td>
													  </tr>	                                      
													 <?php
											  }
											  ?>
											  </tbody>
											  </table>
											  <br />
											  </td>
											  </tr>
											  </table>
		
										<!-- LIST GRID:begin -->     
							<br />

                    
							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                <table class="botones2">
                                  <tr>
                                    <td class="botonstandard">
                                    <img src="images/bulletcancel.png" />&nbsp;
                                    <a href="?m=helpdesk&s=bonusrecord&a=delete&t=bonusrecord&n=<?php echo $affiliationid; ?>&cardnumber=<?php echo $cardnumber; ?>&q=<?php echo $saleid; ?>&actionauth=<?php echo $actionauth; ?>"  onclick="return confirm('La Transacción será ELIMINADA. Esta acción no puede deshacerse. Confirmas que deseas eliminarla?')">Eliminar Transacci&oacute;n</a>
                                    </td>
                                  </tr>
                                </table>
                            <br /><br />                    
                            <?php } ?>
                  
                                
						<?php                                
						} 
                        ?>        
                                
<script type="text/javascript">
   jQuery(document).ready(function() {
     jQuery("abbr.timeago").timeago();
   });
</script>
            
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
