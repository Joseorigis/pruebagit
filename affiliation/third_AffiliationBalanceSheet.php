<?php

// ----------------------------------------------------------------------------------------------------
// RULES BONUS TO EXPIRE
// ----------------------------------------------------------------------------------------------------

	// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
		//header ('X-Frame-Options: DENY');
		header ('X-Frame-Options: SAMEORIGIN');

	// WARNINGS & ERRORS
		ini_set('error_reporting', E_ALL&~E_NOTICE);
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

	// SCRIPT
		// Obtengo el nombre del script en ejecución
		$script = __FILE__;
		$camino = get_included_files();
		$scriptactual = $camino[count($camino)-1];
	

	// INCLUDES & REQUIRES 
		include_once('../includes/configuration.php');	// Archivo de configuración
		include_once('../includes/functions.php');	// Librería de funciones
		include_once('../includes/database.class.php');	// Class para el manejo de base de datos
		include_once('../includes/databaseconnection.php');	// Conexión a base de datos

		include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos


// --------------------
// INICIO CONTENIDO
// --------------------
	
	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();
		// MESSAGE & KEY
		$message = "";
		$requiredkey  = "9755e257abd8f3ea0c3a452639054a00";
		$webservice = str_replace(getCurrentPageScript(), '', getCurrentPageURL());

	
	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}



	// PARAMETER VALIDATION
		// key
			$key = '0';
			if (isset($_GET['k'])) { 
				$key = setOnlyText($_GET['k']); 
			}
			if ($key !== $requiredkey) { $actionerrorid = 10; }
			if ($key == '') { $actionerrorid = 2; }


		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}
		
		// Obtenemos la tarjeta a consultar
		if (!isset($cardnumber)) { $cardnumber = ''; }
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyText($_GET['cardnumber']);
			if ($cardnumber == '') { $cardnumber = '0'; }
		}



		$connectionappkey = "NA";
		if (isset($configuration['appkey'])) { $connectionappkey = $configuration['appkey']; }
		if ($actionerrorid == 0) { 
			$connectionappkey = "romatarjeta"; 
		}

		$cardnumberrelated = $cardnumber;
		if ($connectionappkey == "romatarjeta") { $cardnumberrelated = "295".$cardnumber; }

		if ($actionerrorid == 2) { 
			$cardnumber = "0"; 
			$cardnumberrelated = "0"; 
		}


	// TRANSACTIONS DATABASE
		include_once('../includes/databaseconnectiontransactions.php');

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();


		// TRANSACTIONS BUTTONS & SUMMARY
			// All Transactions button flag...
			$AllTransactionsButton = 0;

			// ConnectionId 10 [FAHORRO]
			$Connection10Link 		= 0;
			$Connection10LinkURL 	= "?m=affiliation&s=itemslinkmda&a=link&n=".$itemid."&cardnumber=".$cardnumber."";
	
			// ConnectionId 13 [BENAVIDES]
			$Connection13Link 		= 0;
			$Connection13LinkURL 	= "https://storage.orveecrm.com/".str_replace('main','',$configuration['appkey'])."/setAffiliationBenavidesExport.php?n=".$cardnumber."";

			// CardsRelated & Bonus Pending flag...
			$CardsRelated = '';
			$TicketsOffline = 0;
			$CardsBonusPending = 0;
	
?>

                <!-- AFFILIATION ITEM BALANCESHEET -->
			<?php
// --------------------------------------------------
// TRANSACTIONS SUMMARY
// --------------------------------------------------
				
				$records = 0;
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceSheet
								'0', '".$cardnumberrelated."',
								'0', 
								'".$connectionappkey."',
								'balancesummary';";
				$dbtransactions->query($query);
				$records = $dbtransactions->count_rows();
				if ($records > 0) {
					$my_row=$dbtransactions->get_row();
					if (isset($my_row['Connection10']))
						{ $Connection10Link = $my_row['Connection10']; }
					if (isset($my_row['Connection13']))
						{ $Connection13Link = $my_row['Connection13']; }
					if (isset($my_row['CardsRelated']))
						{ $CardsRelated = trim($my_row['CardsRelated']); }
					if (isset($my_row['BonusPending']))
						{ $CardsBonusPending = $my_row['BonusPending']; }
					if (isset($my_row['TicketsOffline']))
						{ $TicketsOffline = $my_row['TicketsOffline']; }
					
				}

			?>                
                
    
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title><?php echo $configuration['apptitle']; ?> | Balance Sheet</title>

	<meta name=viewport content="width=device-width, initial-scale=1">
    
    <link rel="shortcut icon" href="../favicon.ico" />
    <link rel="apple-touch-icon" href="../apple-touch-icon.png" />
    
    <link href="../style.css" rel="stylesheet" type="text/css" />
	<link href="../dropdownstyles.css" rel="stylesheet" type="text/css" />
	<link href="../jdpicker.css" rel="stylesheet" type="text/css" media="screen" />
    
   		<script type="text/javascript" src="../includes/jquery.min.js"></script>
		<script type="text/javascript" src="../includes/formcheck.js"></script>
		<script type="text/javascript" src="../includes/jquery.tipsy.js"></script>
        <script type="text/javascript" src="../includes/jquery.timeago.js"></script>
		<script type="text/javascript" src="../includes/jquery.jdpicker.js"></script>
       
	        
</head>

<body id="mainbody" style="background-color: #ffffff;">
                                           
                <?php

				// Init de items contador...
				$items = 0;
	// ------------------------------------------------------------------------------------
	// CARDNUMBER BONUS PENDING: begin
	// ------------------------------------------------------------------------------------
				// Obtenemos los registros...
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceBonusPending
								'0',
								'".$cardnumberrelated."',
								'0', 
								'".$connectionappkey."';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows(); 	// Total de elementos
				// si hay registros...
				if ($items > 0) {
                ?>
                                      
                    <table class="tablelistitems">
                      <thead>
                      <tr style="background-color:#ffffff;">
                        <td colspan="6">
                        <span style="font-size:12px;font-weight:normal;">
                        Bonificaciones Pendientes 
						<span style="font-size:11px;font-weight:bold;color:#ADB1BD;"><?php echo $cardnumber; ?></span>
                        </span>
                      </tr>
                      <tr style="background-color:#b3e6ff;">
                        <td align="left">Transacci&oacute;n</td>
                        <td align="left">Ubicaci&oacute;n</td>
                        <td align="left">Ticket</td>
                        <td align="left">Fecha</td>
                        <td align="left">Art&iacute;culo</td>
                        <td align="left">Status</td>
                      </tr>
                      </thead>
                      <tbody>
						<?php
                        while($my_row=$dbtransactions->get_row()){ 
                                  ?>
                                  <tr style="background-color:#e6f7ff;">
                                    <td>
                                        <span style="font-size:8px;">
                                        BONIFICACION PENDIENTE<br />
                                        <?php echo $my_row['TransactionNo']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['StoreBrand']; ?><br />
                                        [<?php echo $my_row['ConnectionId']; ?>]&nbsp;
                                        <span style="font-size:8px;font-style:italic;">@ Sucursal <?php echo $my_row['StoreId']; ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['InvoiceNumber']; ?><br />
                                        <?php echo $my_row['InvoiceDate']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['TransactionDate']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['Item']; ?><br />
                                        <span style="font-style:italic;">
                                        <?php echo $my_row['ItemName']; ?>&nbsp;
                                        <?php if ($configuration['appkey'] == 'orbisportalmain') {
									  			echo '['.$my_row['ItemBrand'].']';
								  		} 
										?>
                                        </span>
                                        </span>
                                    <td>
                                        <span style="font-size:10px;font-style:italic;color:#F00;">
                                        <?php echo $my_row['Quantity']; ?> caja(s) BONIF. &nbsp;
											<!--<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
                                                    $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                                    <a href="?m=helpdesk&s=bonusfree&a=delete&t=bonusfree&cardnumber=<?php echo $cardnumber; ?>&connectionid=<?php echo $my_row['ConnectionId']; ?>&itemsku=<?php echo $my_row['Item']; ?>&units=1&actionauth=<?php echo $actionauth; ?>"  onclick="return confirm('La Bonificacion será ELIMINADA. Esta acción no puede deshacerse. Confirmas que deseas eliminarla?')" title="Eliminar Bonificacion"><img src="../images/bulletcancel.png" /></a>
                                            <?php } ?> -->                                       
                                        </span>                                    
                                    </td>
                                  </tr>                                                      
                                 <?php
                          } // [while($my_row=$dbtransactions->get_row()){ ]
                          ?>
                          <tr style="background-color:#ffffff;">
                            <td align="right" colspan="6">
                            <span style="font-size:9px; font-style:italic;">
                            * Bonificaciones pendientes a entregar en la siguiente compra de la tarjeta.
                            </span>
                            </td>
                          </tr>
                      </tbody>
                      </table>
                      <br />
                <?php
				} // [if ($items > 0) {]
	// ------------------------------------------------------------------------------------
	// CARDNUMBER BONUS PENDING: end
	// ------------------------------------------------------------------------------------
				?>

                <?php

				// Init de items contador...
				$items = 0;
	// ------------------------------------------------------------------------------------
	// CARDNUMBER TICKETS OFFLINE: begin
	// ------------------------------------------------------------------------------------
				// Obtenemos los registros...
				$query  = "EXEC dbo.usp_app_HelpDeskTicketsOfflineManage
								'0', 
								'".$connectionappkey."',
								'listbycardnumber', '0', '0', 
								'".$cardnumberrelated."';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows(); 	// Total de elementos
				// si hay registros...
				if ($items > 0) {
                ?>
                    <table class="tablelistitems">
                      <thead>
                      <tr style="background-color:#ffffff;">
                        <td colspan="6">
                        <span style="font-size:12px;font-weight:normal;">
                        Tickets Offline 
						<span style="font-size:11px;font-weight:bold;color:#ADB1BD;"><?php echo $cardnumber; ?></span>
                        </span>
                        </td>
                      </tr>
                      <tr style="background-color:#b3e6cc;">
                        <td align="left">Transacci&oacute;n</td>
                        <td align="left">Ubicaci&oacute;n</td>
                        <td align="left">Ticket</td>
                        <td align="left">Fecha</td>
                        <td align="left">Art&iacute;culo</td>
                        <td align="left">Status</td>
                      </tr>
                      </thead>
                      <tbody>
						<?php
                        while($my_row=$dbtransactions->get_row()){ 
                                  ?>
                                  <tr style="background-color:#e6ffe6;">
                                    <td>
                                        <span style="font-size:8px;">
                                        TICKET OFFLINE<br />
                                        <?php echo $my_row['CaseNumber']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['ConnectionName']; ?>&nbsp;
                                        [<?php echo $my_row['ConnectionId']; ?>]
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['InvoiceNumber']; ?><br />
                                        <?php echo $my_row['InvoiceDate']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
                                        <?php echo $my_row['RecordDate']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size:9px;">
										<?php echo $my_row['ItemQuantity']; ?>x . 
                                        <?php echo $my_row['ItemSKU']; ?><br />
                                        <span style="font-style:italic;">
                                        <?php echo $my_row['ItemName']; ?>
                                        </span>
                                        </span>
                                    <td>
                                    	<?php echo $my_row['TicketOfflineStatus']; ?>
                                    </td>
                                  </tr>                                                      
                                 <?php
                          } // [while($my_row=$dbtransactions->get_row()){ ]
                          ?>
                      </tbody>
                      </table>
                      <br />
                <?php
				} // [if ($items > 0) {]
	// ------------------------------------------------------------------------------------
	// CARDNUMBER TICKETS OFFLINE: end
	// ------------------------------------------------------------------------------------
				?>

            <table class="tableaffiliatedtab">
              <thead>
              <tr>
                <td colspan="4">
                Historial 
				<span style="font-size:18px;font-weight:bold;color:#ADB1BD;"><?php echo $cardnumber; ?></span>
                </td>
                <?php if ($CardsRelated == '') { ?>
                    <td colspan="2" align="right">&nbsp;
                        
                    </td>
                <?php } else { ?>
                    <td colspan="2" align="right" style="background-color:#f0f0f0;border-top: 1px solid #ADB1BD;font-size:9px;">
                    	* Tarjeta con v&iacute;nculo&nbsp;
                        <span style="font-size:12px;">
                    	<a href="#" target="_blank" title="Ver Tarjeta">
                        <?php echo $CardsRelated; ?>
                        </a>
                        </span>
                    </td>
                <?php } ?>
              </tr>
              <tr class="tableaffiliatedtabheadertr">
                <td class="tableaffiliatedtabheadertd">Transacci&oacute;n</td>
                <td class="tableaffiliatedtabheadertd">Ubicaci&oacute;n</td>
                <td class="tableaffiliatedtabheadertd">Ticket</td>
                <td class="tableaffiliatedtabheadertd">Fecha</td>
                <td class="tableaffiliatedtabheadertd">Art&iacute;culo</td>
                <td class="tableaffiliatedtabheadertd">Cantidad</td>
              </tr>
              <thead>
              </thead>
              <tbody>
                <?php
	
				// Init de items contador...
				$items = 0;
				$TransactionsTotal = 0;
				$TransactionsDateNow = date('Ymd H:i:s');
				$TransactionColor = 'f0f0f0';
				$TransactionRecency = 99;
				
// --------------------------------------------------
// TRANSACTIONS LIST [BEGIN]
// --------------------------------------------------

                // get items or records...
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceSheet
								'0', '".$cardnumberrelated."',
								'0', 
								'".$connectionappkey."';";
				$dbtransactions->query($query);
                //$items = $items + $dbtransactions->count_rows(); 	// get items count
				
				// get items one by one
                while($my_row=$dbtransactions->get_row()){ 
					$items = $items + 1; // items counter
					
						// transactions total & date
							$TransactionsTotal 		= $my_row['TransactionsTotal'];
							$TransactionsDateNow 	= $my_row['TransactionsDateNow'];
							$TransactionSign		= '';

						// row background & color set
							$color = '';
							$invoiceflag = '';
							$invoiceflag = '['.$my_row['TransactionType'].']';
								// OFFLINE
								if ($my_row['TransactionTypeId'] == '2' || $my_row['TransactionTypeId'] == '4' ) {
									$color = ' bgcolor="#fffce6" '; 
									$invoiceflag = '[OFFLINE]';
								}
								// DEVOLUCIONES
								if ($my_row['TransactionTypeId'] == '3') {
									$color = ' bgcolor="#ffe6e6" '; 
									$TransactionSign = '-';
								}

						// transactioncolor
							$TransactionRecency = $my_row['TransactionRecency'];
							if ($TransactionRecency < 3)
								{ $TransactionColor = '89C402'; }
							if ($TransactionRecency >= 3  && $TransactionRecency < 13)
								{ $TransactionColor = 'FCD60C'; }
							if ($TransactionRecency > 12)
								{ $TransactionColor = 'EC1210'; }
										
							?>
						  <tr <?php echo $color; ?>>
							<td style="border-left: 5px solid #<?php echo $TransactionColor; ?>;">
                                <span style="font-size:8px;">
                                No: 
                                <a href="#" target="_blank" title="Ver Transacci&oacute;n Detalle">
								<?php echo $my_row['TransactionNo']; ?><br />
                                </a>
                                Auth: 
                                <a href="#" target="_blank" title="Ver Transacci&oacute;n Detalle">
                                <?php echo $my_row['SaleAuthNumber']; ?>
                                </a>
                                </span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $my_row['StoreBrand']; ?><br />
                                [<?php echo $my_row['ConnectionId']; ?>]&nbsp;
                                <span style="font-size:8px;font-style:italic;">@ Sucursal <?php echo $my_row['StoreId']; ?></span>
                                </span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $my_row['InvoiceNumber']; ?>
                                <br /><span style="font-weight:bold;font-size:8px;"><?php echo $invoiceflag; ?></span>
                                </span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $my_row['TransactionDate']; ?>
                                </span>
							</td>
							<td>
								<a href="#" target="_blank" title="Ver Regla Negocio" >
                               <?php echo $my_row['Item']; ?></a><br />
                                <span style="font-size:9px;font-style:italic;">
								<?php echo $my_row['ItemName']; ?>&nbsp;
                                        <?php if ($configuration['appkey'] == 'orbisportalmain') {
									  			echo '['.$my_row['ItemBrand'].']';
								  		} 
										?></span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $TransactionSign; ?> <?php echo $my_row['Quantity']; ?> caja(s)
                                <?php if ($my_row['QuantityBonus'] > 0) { ?>
                                    <br />
                                    <span style="font-size:9px;font-style:italic;color:#00F;">
                                    <?php echo $my_row['QuantityBonus']; ?> caja(s) BONIF
                                    </span>
                                <?php } ?>
                                <?php if ($my_row['Discount'] > 0) { ?>
                                    <br />
                                    <span style="font-size:9px;font-style:italic;color:#F00;">
                                    <?php echo $my_row['Discount']; ?>% descuento
                                    </span>
                                <?php } ?>
                                </span>
							</td>
						  </tr>
						 <?php
                             
                  } // [while($my_row=$dbtransactions->get_row()){ ]
 
 
// --------------------------------------------------
// TRANSACTIONS LIST [END]
// --------------------------------------------------
 
 				// Activamos el botón de Ver Todas Transacciones
 				if ($TransactionsTotal > $items)
					{$AllTransactionsButton = 1; }
 
                  
// --------------------------------------------------
// TRANSACTIONS RELATED LIST [BEGIN]
// --------------------------------------------------
                  
                    $hayvinculo = 0;
                    
                    // Obtengo los registros...
                    $query  = "EXEC dbo.usp_app_AffiliationCardBonding
                                    '0', '".$connectionappkey."',
                                    'view','crm',
                                    '0', '".$cardnumberrelated."';";
                    $dbconnection->query($query);
					
                    while($my_bond=$dbconnection->get_row()){ 
                
                                    $hayvinculo = $hayvinculo + 1;
                
                                    $itemslocal = 0;
                                    // Obtengo el índice del paginado
                                    $query  = "EXEC dbo.usp_app_AffiliationItemBalanceSheet
                                                    '0', '".$my_bond['CardRelated']."',
                                                    '0', '".$configuration['appkey']."';";
                                    $dbtransactions->query($query);
                                    $itemslocal = $dbtransactions->count_rows(); 	// Total de elementos
                                    
                                    //if ($itemslocal > 0) {
                                        ?>
                                        <tr>
                                        <td align="center" colspan="6" style="background-color:#cce6ff;">
                                        <div align="left">
                                            <span style="font-size:12px;font-weight:bold;"><?php echo $my_bond['CardRelated']; ?></span> Historial V&iacute;nculo
                                            <span style="font-style:italic; font-size:9px;">
                                            &nbsp;&nbsp;
                                            [Vinculada en <?php echo $my_bond['RelatedDate']; ?> a las <?php echo $my_bond['RelatedTime']; ?> hrs]
                                            </span>
                                        </div>
                                        </td>
                                        </tr>
                                        <?php
                                    //}
                                
                                // Imprimimos en pantalla cada uno de los parámetros
                                while($my_row=$dbtransactions->get_row()){ 
                                                $items = $items + 1;

												// transactions total & date
													$TransactionsTotal 		= 0;
													$TransactionsDateNow 	= $my_row['TransactionsDateNow'];
													$TransactionSign		= '';
						
												// row background & color set
													$color = '';
													$invoiceflag = '';
													$invoiceflag = '['.$my_row['TransactionType'].']';
														// OFFLINE
														if ($my_row['TransactionTypeId'] == '2' || $my_row['TransactionTypeId'] == '4' ) {
															$color = ' bgcolor="#fffce6" '; 
															$invoiceflag = '[OFFLINE]';
														}
														// DEVOLUCIONES
														if ($my_row['TransactionTypeId'] == '3') {
															$color = ' bgcolor="#ffe6e6" '; 
															$TransactionSign = '-';
														}
						
												// transactioncolor
													$TransactionRecency = $my_row['TransactionRecency'];
													if ($TransactionRecency < 3)
														{ $TransactionColor = '89C402'; }
													if ($TransactionRecency > 3 && $TransactionRecency < 13)
														{ $TransactionColor = 'FCD60C'; }
													if ($TransactionRecency > 12)
														{ $TransactionColor = 'EC1210'; }
																						
                                                ?>
						  					<tr <?php echo $color; ?>>
												<td style="border-left: 5px solid #<?php echo $TransactionColor; ?>;">
                                                    <span style="font-size:8px;">
                                                    No: 
                                                    <a href="#" target="_blank" title="Ver Transacci&oacute;n Detalle">
                                                    <?php echo $my_row['TransactionNo']; ?><br />
                                                    </a>
                                                    Auth: 
                                                    <a href="#" target="_blank" title="Ver Transacci&oacute;n Detalle">
                                                    <?php echo $my_row['SaleAuthNumber']; ?>
                                                    </a>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style="font-size:9px;">
                                                    <?php echo $my_row['StoreBrand']; ?><br />
                                                    [<?php echo $my_row['ConnectionId']; ?>]&nbsp;
                                                    <span style="font-size:8px;font-style:italic;">@ Sucursal <?php echo $my_row['StoreId']; ?></span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style="font-size:9px;">
                                                    <?php echo $my_row['InvoiceNumber']; ?>
                                                    <br /><span style="font-weight:bold;"><?php echo $invoiceflag; ?></span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style="font-size:9px;">
                                                    <?php echo $my_row['TransactionDate']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="#" target="_blank" title="Ver Regla Negocio" >
                                                    <?php echo $my_row['Item']; ?></a><br />
                                                    <span style="font-size:9px;font-style:italic;">
                                                    <?php echo $my_row['ItemName']; ?>&nbsp;
															<?php if ($configuration['appkey'] == 'orbisportalmain') {
																	echo '['.$my_row['ItemBrand'].']';
															} 
															?></span>
                                                </td>
                                                <td>
                                                    <span style="font-size:9px;">
                                                    <?php echo $TransactionSign; ?> <?php echo $my_row['Quantity']; ?> caja(s)
                                                    <?php if ($my_row['QuantityBonus'] > 0) { ?>
                                                        <br />
                                                        <span style="font-size:9px;font-style:italic;color:#00F;">
                                                        <?php echo $my_row['QuantityBonus']; ?> caja(s) BONIF
                                                        </span>
                                                    <?php } ?>
                                                    <?php if ($my_row['Discount'] > 0) { ?>
                                                        <br />
                                                        <span style="font-size:9px;font-style:italic;color:#F00;">
                                                        <?php echo $my_row['Discount']; ?>% descuento
                                                        </span>
                                                    <?php } ?>
                                                    </span>
                                                </td>
                                              </tr>
                                             <?php
                                             
                                  }
                                  // Si no hay elementos a mostrar..
                                  if ($itemslocal == 0) {
                                      ?>
                                    <tr>
                                    <td align="center" colspan="6">
                                    <div align="center"><em>Sin Historial en V&iacute;nculo</em></div>
                                    </td>
                                    </tr>
                                      <?php
                                  } 
                                  
                                  
                    }
                    
				// if NO items, set message
					if ($items == 0) {
					?>
                        <tr>
                        <td align="center" colspan="6">
                        <div align="center"><em>Sin Transacciones</em></div>
                        </td>
                        </tr>
					<?php
					} 

// --------------------------------------------------
// TRANSACTIONS RELATED LIST [END]
// --------------------------------------------------
				 
              ?>
              </tbody>
            </table>
            
            <table width="90%" border="0" cellspacing="3" align="center">
              <tr>
                <td align="right">
                <span style="color:#cccccc;font-style:italic;font-size:11px;">
                * A <?php echo $TransactionsDateNow; ?> hrs.
                </span>
                </td>
              </tr>
            </table>
                
            <br />  
            <!--<table class="botones" align="center">
                <tr>
                <td class="botonstandard" style="font-size:9px;">
                <img src="../images/bulletsenderid.png" />&nbsp;
                <a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid=<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>&actionauth=0&cardnumber=<?php echo $cardnumber; ?>" target="_blank" title="Ver Historial Sucursal Tarjeta">Historial Sucursal</a>
                </td>
                <?php if ($AllTransactionsButton == 1) { ?>
                    <td class="botonstandard" style="font-size:9px;">
                    <img src="../images/bulletlist2.png" />&nbsp;
                    <a href="?m=reports&s=transactions&a=list&t=cardnumber&q=<?php echo $cardnumber; ?>" target="_blank" title="Ver Todas las Transacciones">Ver Todas</a>
                    </td>
                <?php } ?>    
                <?php if ($Connection10Link == 1) { ?>
                    <td class="botonstandard" style="font-size:9px;">
                    <img src="../images/bulletheaderswitchon.png" />&nbsp;
                    <a href="<?php echo $Connection10LinkURL; ?>" target="_blank" title="Vincular Tarjeta con Monedero">Vincular Monedero Del Ahorro</a>
                    </td>
                <?php } ?>    
                <?php if ($Connection13Link == 1) { ?>
                    <td class="botonstandard" style="font-size:9px;">
                    <img src="../images/bulletrefresh.png" />&nbsp;
                    <a href="<?php echo $Connection13LinkURL; ?>" target="_blank" title="Activar Tarjeta en Benavides">Republicar Afiliaci&oacute;n Benavides</a>
                    </td>
                <?php } ?>    
                </tr>
            </table> -->
            
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
                <?php include_once('../includes/databaseconnectionrelease.php'); ?>
				<!-- AFFILIATION ITEM BALANCESHEET -->
</body>
</html>                            