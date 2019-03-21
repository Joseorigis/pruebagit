<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* affiliation_x.php
* 	Despliega una lista de elementos, incluyendo el paginado.
*
* @version 20161018
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
	
	
// --------------------
// INICIO CONTENIDO
// --------------------


	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", getCurrentPageScript());
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.getCurrentPageScript());


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		//$itemid = 0;
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


	// TRANSACTIONS DATABASE
		include_once('../includes/databaseconnectiontransactions.php');

	// INIT 
		// ERROR ID ... inicializamos el indicador del error en el proceso
		$actionerrorid = 0;
		// AUTHNUMBER for duplicate check
		$actionauth = getActionAuth();


		// DATABASE TRANSACTIONS
		// Connecting to database to TRANSACTIONS & POINTS
		$dbitems = new database($configuration['db2type'],
							$configuration['db2host'], 
							$configuration['db2name'],
							$configuration['db2username'],
							$configuration['db2password']);

			// CardsRelated & Bonus Pending flag...
			$CardsRelated = '';
			$CardsBonusPending = 0;
	
?>

                <!-- AFFILIATION ITEM BALANCEITEMS -->
			<?php
// --------------------------------------------------
// TRANSACTIONS SUMMARY
// --------------------------------------------------
				
				$records = 0;
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceSheet
								'0', '".$cardnumber."',
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."',
								'balancesummary';";
				$dbtransactions->query($query);
				$records = $dbtransactions->count_rows();
				if ($records > 0) {
					$my_row=$dbtransactions->get_row();
					if (isset($my_row['CardsRelated']))
						{ $CardsRelated = trim($my_row['CardsRelated']); }
					if (isset($my_row['BonusPending']))
						{ $CardsBonusPending = $my_row['BonusPending']; }
					
				}

			?>                
             
                
                      
                
                <?php

				// Init de items contador...
				// Init de items contador...
				$items = 0;
				$TransactionsDateNow = date('Ymd H:i:s');
				$TransactionColor = 'f0f0f0';
				$TransactionRecency = 99;
				
				$itemgroup 			= '0';
				$itemunits 			= 0;
				$itemunitsdiscount 	= 0;
				$itemunitsbonus 	= 0;
				$itembonuslimited 	= 0;
				$itembonus12m 		= 0;
	// ------------------------------------------------------------------------------------
	// CARDNUMBER ITEMSGROUPS: begin
	// ------------------------------------------------------------------------------------
				// Obtenemos los registros...
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceItems
								'0',
								'".$cardnumber."',
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."',
								'index';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows(); 	// Total de elementos
				// si hay registros...
				if ($items > 0) {
                ?>
						<?php
                        while($my_row=$dbtransactions->get_row()){ 

								$itemgroup 			= '0';
								$itemunits 			= 0;
								$itemunitsdiscount 	= 0;
								$itemunitsbonus 	= 0;
								$itembonuslimited 	= 0;
								$itembonus12m 		= 0;	
							
								$itemgroup 			= $my_row['ItemGroupId'];
								$itembonuslimited 	= 0;
								$itembonus12m 		= $my_row['UnitsBonus12'];

							
						// transactioncolor
							$TransactionRecency = $my_row['TransactionRecency'];
							if ($TransactionRecency < 3)
								{ $TransactionColor = '89C402'; }
							if ($TransactionRecency >= 3  && $TransactionRecency < 13)
								{ $TransactionColor = 'FCD60C'; }
							if ($TransactionRecency > 12)
								{ $TransactionColor = 'EC1210'; }
							
                                  ?>

            

            <table class="tableaffiliatedtab">
              <thead>
              <tr>
                <td colspan="2">
                Art&iacute;culo 
				<span style="font-size:18px;font-weight:bold;color:#ADB1BD;"><?php echo $my_row['ItemGroupId']; ?></span>&nbsp;
				<span style="font-size:18px;font-weight:bold;color:#ADB1BD;"><?php echo $my_row['ItemBrand']; ?></span>
                </td>
                <td>
                <span style="font-size:10px;">
				<?php echo $my_row['Units']; ?> cajas <br />
				<?php echo $my_row['UnitsBonus']; ?> bonificaciones<br />
				<?php echo $my_row['UnitsDiscount']; ?> descuentos<br />
               </span>
                </td>
                <td>
                <span style="font-size:10px;">
				<?php echo $my_row['UnitsBonus12']; ?> bonifs 12m <br />
				<?php echo $my_row['FirstBonus']; ?> Primera<br />
				<?php echo $my_row['NextBonus']; ?> Siguiente<br />
               </span>
                </td>
				<td colspan="2" align="right" style="background-color:#f0f0f0;border-top: 1px solid #ADB1BD;font-size:9px;">
					<span style="font-size:12px;">
					* <?php echo $my_row['LastTransaction']; ?>
					</span>
				</td>
              </tr>
              
                <?php
	
// --------------------------------------------------
// RULESBONUS [BEGIN]
// --------------------------------------------------

                // get items or records...
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceItems
								'0', '".$cardnumber."',
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."',
								'rules',
								'".$itemgroup."';";//echo $query;
				$dbitems->query($query);
                //$items = $items + $dbitems->count_rows(); 	// get items count
				
				// get items one by one
                while($rowitem=$dbitems->get_row()){ 
					$items = $items + 1; // items counter
					
							if ($itembonus12m < $rowitem['TransactionsRangeTo']) {
								$itembonuslimited = 0;
							} else {
								$itembonuslimited = 1;
							}
					
							?>
						  <tr>
							<td style="font-size:10px;">
								<?php echo $rowitem['RuleDescription']; ?><br />
								<?php echo $rowitem['RuleLimit']; ?>
								<?php
									if ($itembonuslimited == 1) { echo "<br />LIMITADO"; }
								?>
							</td>
							<td style="font-size:10px;">
								<?php echo $rowitem['ItemSKUBonus']; ?>
							</td>
							<td style="font-size:10px;">
								<?php echo $rowitem['ConnectionName']; ?> [<?php echo $rowitem['ConnectionId']; ?>]
							</td>
							<td style="font-size:10px;">
								<?php echo $rowitem['RulePublishStatus']; ?>
							</td>
							<td colspan="2" style="font-size:10px;">
									Vigente del <?php echo $rowitem['RuleActivation']; ?> a <?php echo $rowitem['RuleExpiration']; ?>
							</td>
						  </tr>
						 <?php
                             
                  } // [while($my_row=$dbtransactions->get_row()){ ]
 
 
// --------------------------------------------------
// RULEBONUS [END]
// --------------------------------------------------
 				 
              ?>
              
              <tr class="tableaffiliatedtabheadertr">
                <td class="tableaffiliatedtabheadertd">Art&iacute;culo</td>
                <td class="tableaffiliatedtabheadertd">Cadena</td>
                <td class="tableaffiliatedtabheadertd">Libres</td>
                <td class="tableaffiliatedtabheadertd">Fecha</td>
                <td class="tableaffiliatedtabheadertd">ItemId</td>
                <td class="tableaffiliatedtabheadertd">Unidades</td>
              </tr>
              <thead>
              </thead>
              <tbody>
               
               
                <?php
	
// --------------------------------------------------
// ITEMS LIST [BEGIN]
// --------------------------------------------------

                // get items or records...
				$query  = "EXEC dbo.usp_app_AffiliationItemBalanceItems
								'0', '".$cardnumber."',
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$configuration['appkey']."',
								'summary',
								'".$itemgroup."';";//echo $query;
				$dbitems->query($query);
                //$items = $items + $dbitems->count_rows(); 	// get items count
				
				// get items one by one
                while($rowitem=$dbitems->get_row()){ 
					$items = $items + 1; // items counter
					
						// transactions total & date
							$TransactionsDateNow 	= $rowitem['TransactionsDateNow'];
					
						// units count
							$itemunits 			= $itemunits + $rowitem['Units'];
							$itemunitsdiscount 	= $itemunitsdiscount + $rowitem['UnitsDiscount'];
							$itemunitsbonus 	= $itemunitsbonus + $rowitem['UnitsBonus'];

										
							?>
						  <tr <?php echo $color; ?>>
							<td style="border-left: 5px solid #<?php echo $TransactionColor; ?>;">
								<?php echo $rowitem['Item']; ?><br />
                                <span style="font-size:9px;font-style:italic;">
								<?php echo $rowitem['ItemName']; ?>&nbsp;
								</span>
							</td>
							<td>
								<?php echo $rowitem['ConnectionName']; ?> [<?php echo $rowitem['ConnectionId']; ?>]
							</td>
							<td>
                                <?php echo $rowitem['UnitsFreeForBonus']; ?>.<?php echo $rowitem['UnitsInvalid']; ?>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $rowitem['LastTransaction']; ?>
                                </span>
							</td>
							<td>
                                <?php echo $rowitem['ItemId']; ?>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $rowitem['Units']; ?> caja(s)
                                <?php if ($rowitem['UnitsBonus'] > 0) { ?>
                                    <br />
                                    <span style="font-size:9px;font-style:italic;color:#00F;">
                                    <?php echo $rowitem['UnitsBonus']; ?> caja(s) BONIF
                                    </span>
                                <?php } ?>
                                <?php if ($rowitem['UnitsDiscount'] > 0) { ?>
                                    <br />
                                    <span style="font-size:9px;font-style:italic;color:#F00;">
                                    <?php echo $rowitem['UnitsDiscount']; ?> descuentos
                                    </span>
                                <?php } ?>
                                </span>
							</td>
						  </tr>
						 <?php
                             
                  } // [while($my_row=$dbtransactions->get_row()){ ]
 
					?>
					
						  <tr <?php echo $color; ?> style="background-color:#f0f0f0;">
							<td colspan="3">
                                &nbsp;
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $itemunits; ?> caja(s)
								</span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $itemunitsdiscount; ?> caja(s)
								</span>
							</td>
							<td>
                                <span style="font-size:9px;">
                                <?php echo $itemunitsbonus; ?> caja(s)
								</span>
							</td>
						  </tr>
					
					<?php
 
// --------------------------------------------------
// ITEMS LIST [END]
// --------------------------------------------------
 				 
              ?>
              </tbody>
            </table>
 
                      <br /><br />
 
                                 
                                 <?php
                          } // [while($my_row=$dbtransactions->get_row()){ ]
                          ?>
                      <br />
                <?php
				} // [if ($items > 0) {]
	// ------------------------------------------------------------------------------------
	// CARDNUMBER ITEMSGROUPS: end
	// ------------------------------------------------------------------------------------
				?>
                                                       
                                  
            <table width="90%" border="0" cellspacing="3" align="center">
              <tr>
                <td align="right">
                <span style="color:#cccccc;font-style:italic;font-size:11px;">
                * A <?php echo $TransactionsDateNow; ?> hrs.
                </span>
                </td>
              </tr>
            </table>
                
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
				<!-- AFFILIATION ITEM BALANCEITEMS -->
                            