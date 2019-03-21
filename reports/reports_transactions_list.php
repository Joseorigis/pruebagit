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
		$itemtype = 'cardnumber';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'cardnumber'; }
		}
		$itemtype = strtolower($itemtype);
		
		// Obtenemos el itemid, identificando el elemento a consultar
		$cardnumber = '0';
		if (isset($_GET['q']) && $itemtype == 'cardnumber') {
			$cardnumber = setOnlyText($_GET['q']);
			if ($cardnumber == '') { $cardnumber = 0; }
			//if (!is_numeric($cardnumber)) { $cardnumber = 0; }
		}
	
	

					// Agregamos el USER a la aplicación...
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
											'0', '".$cardnumber."';";
					$dbconnection->query($query);
					$records = $dbconnection->count_rows(); 
					$my_row=$dbconnection->get_row();
					
					$affiliationcard = '';
						if (isset($my_row['Tarjeta'])) {
							$affiliationcard = $my_row['Tarjeta']; 
						}
						if (isset($my_row['CardNumber'])) {
							$affiliationcard = $my_row['CardNumber']; 
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
                            
<script type="text/javascript">
   jQuery(document).ready(function() {
     jQuery("abbr.timeago").timeago();
   });
</script>
            
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
                                <span class="textSmall"><?php echo $affiliationname; ?><br /></span>
                                Transacciones
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
				</table>
                <br />
                    
                    <?php
			
						// GET RECORDS...
						$items = 0;
						$itemindex = 0;
						$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemTransactions
											'0', '".$cardnumber."',
											'".$_SESSION[$configuration['appkey']]['userid']."', 
											'".$configuration['appkey']."',
											'all';";//echo $query;
						$dbtransactions->query($query);
						$items = $dbtransactions->count_rows(); 	// Total de elementos
						?>
                        
                        <!-- LIST GRID:begin -->          
                            <table width="95%">
                            <tr>
                            <td>
                              
                            <table class="tablelistitems">
                              <thead>
                              <tr>
                                <td colspan="7">Transacciones del Afiliado</td>
                              </tr>
                              <tr>
                                <td align="center">#</td>
                                <td align="left">Tipo</td>
                                <td align="left">Transacci&oacute;n</td>
                                <td align="left">Autorizaci&oacute;n</td>
                                <td align="left">Ubicaci&oacute;n</td>
                                <td align="left">Ticket</td>
                                <td align="left">Fecha</td>
                              </tr>
                              </thead>
                              <tbody>
                              
                              <?php                        
					
						if ($items == 0) {
							?>
                            
                                  <tr>
                                    <td align="center" colspan="7">
                                    <span style="font-style:italic;font-size:14px;">
                                    Sin Transacciones
                                    </span>
                                    </td>
                                    </tr>

                            
                            <?php
						} else {
						
							?>
							

                                    <?php
                                    while($my_row=$dbtransactions->get_row()){ 
                                            $itemindex = $itemindex + 1;
                                              ?>
                                              
                                              <tr>
                                                <td align="center">
                                                <span style="font-size:8px;">
                                                <?php echo $itemindex.'.'; ?>
                                                </span>
                                                </td>
                                                <td>
                                                <?php echo $my_row['TransactionType']; ?>
                                                </td>
                                                <td class="itemdetaillistelement">
                                                <a href="?m=reports&s=transactions&a=view&n=0&t=transaction&q=<?php echo $my_row['TransactionNo']; ?>" target="_blank">
                                                <?php echo $my_row['TransactionNo']; ?>
                                                </a>
                                                </td>
                                                <td>
                                                <a href="?m=reports&s=transactions&a=view&n=<?php echo $itemid; ?>&t=sale&q=<?php echo $my_row['SaleAuthNumber']; ?>" target="_blank">
                                                <?php echo $my_row['SaleAuthNumber']; ?>
                                                </a>
                                                </td>
                                                <td>
                                                <?php echo $my_row['StoreBrand']; ?><br />
                                                <span style="font-size:10px;font-style:italic;">@ Sucursal <?php echo $my_row['StoreId']; ?></span>
                                                </td>
                                                <td>
                                                <?php echo $my_row['InvoiceNumber']; ?>
                                                </td>
                                                <td>
                                                <?php echo $my_row['TransactionDate']; ?>
                                                </td>
                                              </tr>                                                      
                                              
                                             <?php
                                      }
                                      ?>
                                
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
