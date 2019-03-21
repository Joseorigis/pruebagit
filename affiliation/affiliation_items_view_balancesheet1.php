<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* affiliation_x.php
* 	Despliega una lista de elementos, incluyendo el paginado.
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
			header("HTTP/1.0 404 Not Found"); 	
			exit();
		}
	} else {
		
		// PAGINA REFERENCIA
			$dondevengo = "";
			$dondevengo = strtolower($_SERVER['HTTP_REFERER']);
			$refpage1 = explode("?",$dondevengo);
			$refpage2 = explode("://",$refpage1[0]);
			if (count($refpage2) > 1) {
				$dondevengo = str_replace('index.php','',$refpage2[1]);
			} else {
				$dondevengo = str_replace('index.php','',$refpage2[0]);
			}
			$dondevengopartes = explode("/",$dondevengo);
			
		// PAGINA SCRIPT ACTUAL	
			// El script actual no debe ser visto por si solo
			$dondeestoyabsoluto = "";
			$dondeestoyabsolutopartes = explode('/', strtolower($_SERVER["SCRIPT_NAME"]));
			$dondeestoyabsoluto = $dondeestoyabsolutopartes[count($dondeestoyabsolutopartes) - 1];
			
		// PAGINA ACTUAL	
			$dondeestoy = "";
			$dondeestoypartes = explode("?",strtolower($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]));
			$dondeestoy = $dondeestoypartes[0];
			
			// Si no vengo del mismo dominio, no paso...
			if (strpos($dondeestoy, $dondevengo) === false) {
				header("HTTP/1.0 404 Not Found"); 	
				exit();
			} 
			//if ($dondeestoyabsoluto <> "index.php") {
			//	header("HTTP/1.0 404 Not Found"); 	
			//	exit();
			//}

	}

	// Verificamos la página que se esta navegando
	if (!isset($appcontainer)) {
		
		// Iniciamos el controlador de SESSIONs de PHP
			session_start();
			
		// INCLUDES & REQUIRES
			include_once('../includes/configuration.php');	// Archivo de configuración
			include_once('../includes/database.class.php');	// Class para el manejo de base de datos
			include_once('../includes/databaseconnection.php');	// Conexión a base de datos
			include_once('../includes/functions.php');	// Librería de funciones
		
	} 
	
// --------------------
// INICIO CONTENIDO
// --------------------

?>

							<!-- AFFILIATION ITEM TARJETAS -->
							<?php
							
                                // Obtengo el índice del paginado
                                $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemBalanceSheet '".$_GET['n']."', '0';";
                                $dbconnection->query($query);
 								$items = $dbconnection->count_rows(); 	// Total de elementos
                            
                            ?>
                            <br />
                            <table class="itemdetail">
                              <thead>
                              <tr>
                                <td colspan="6">Historial del afiliado</td>
                              </tr>
                              <tr>
                                <td class="itemdetailheader">Transacci&oacute;n</td>
                                <td class="itemdetailheader">Ubicaci&oacute;n</td>
                                <td class="itemdetailheader">Ticket</td>
                                <td class="itemdetailheader">Fecha</td>
                                <td class="itemdetailheader">Art&iacute;culo</td>
                                <td class="itemdetailheader">Cantidad</td>
                              </tr>
                              </thead>
                              <tbody>
                              <?php
			  				// Imprimimos en pantalla cada uno de los parámetros
							while($my_row=$dbconnection->get_row()){ 
							
											?>
										  <tr>
											<td class="itemdetaillistelement">
											No: <?php echo $my_row['TransactionNo']; ?><br />
											Auth: <?php echo $my_row['SaleAuthNumber']; ?>
											</td>
											<td class="itemdetaillistelement">
											<?php echo $my_row['StoreBrand']; ?><br />
											<span style="font-size:10px;font-style:italic;">@ Sucursal <?php echo $my_row['StoreId']; ?></span>
											</td>
											<td class="itemdetaillistelement">
											<?php echo $my_row['InvoiceNumber']; ?>
											</td>
											<td class="itemdetaillistelement">
											<?php echo $my_row['TransactionDate']; ?>
											</td>
											<td class="itemdetaillistelement">
											<?php echo $my_row['Item']; ?><br />
											<span style="font-size:10px;font-style:italic;"><?php echo $my_row['ItemName']; ?></span>
											</td>
											<td class="itemdetaillistelement">
											<?php echo $my_row['Quantity']; ?> caja(s)
											<?php if ($my_row['QuantityBonus'] > 0) { ?>
												<br />
												<span style="font-size:10px;font-style:italic;color:#00F;">
												<?php echo $my_row['QuantityBonus']; ?> caja(s) BONIFICACI&Oacute;N
												</span>
											<?php } ?>
											<?php if ($my_row['Discount'] > 0) { ?>
												<br />
												<span style="font-size:10px;font-style:italic;color:#F00;">
												<?php echo $my_row['Discount']; ?>% descuento
												</span>
											<?php } ?>
											</td>
										  </tr>
										 <?php
										 
							  }
							  // Si no hay elementos a mostrar..
							  if ($items == 0) {
								  ?>
                            	<tr>
                                <td class="itemdetaillistelement" align="center" colspan="6">
                                <div align="center"><em>Sin Historial</em></div>
								</td>
                                </tr>
                                  <?php
							  } 
                              ?>
                              </tbody>
                            </table>
                            <br />
                            
                                <table class="botones2" align="center">
                                  <tr>
                                    <td class="botonstandard">
                                    <img src="images/bulletlist2.png" />&nbsp;
                                    <a href="http://historial.orbisfarma.com.mx/index.php?action=balance&key=&storeid=0&posid=0&employeeid=<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>&actionauth=0&cardnumber=<?php echo $_GET['cardnumber']; ?>" target="_blank">Ver Historial [Sucursal]</a>
                                    </td>
                                  </tr>
                                </table> 
                            
                            
                            <br />
                            <table width="90%" border="0" cellspacing="3" align="center">
                              <tr>
                                <td align="right">
                                <span style="color:#F0F0F0;font-style:italic;font-size:9px;">
                                |itemid:<?php echo $_GET['n']; ?>@<?php echo getCurrentPageScript(); ?>|
                                </span>
                                </td>
                              </tr>
                            </table>
                            <br />
							<!-- AFFILIATION ITEM TARJETAS -->
                            