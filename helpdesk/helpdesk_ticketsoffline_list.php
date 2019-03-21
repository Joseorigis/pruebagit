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

			// TRANSACTIONS DATABASE	
				include_once('../includes/databaseconnectiontransactions.php');

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
	

// --------------------
// INICIO CONTENIDO
// --------------------

	// CURRENT PAGE SCRIPT
		$listscriptparts = explode(chr(92), $scriptactual);
		$listscript = $listscriptparts[count($listscriptparts)-1];

	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", $listscript);
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.$listscript);


		// ItemType BONUS / POINTS / WARNINGS
			if (isset($itemtype)) {
				if (trim($itemtype) == '') { $itemtype = 'TICKETSOFFLINE'; }
			} else {
				$itemtype = 'TICKETSOFFLINE';
				if (isset($_GET['t'])) {
					$itemtype = setOnlyLetters($_GET['t']);
					if (trim($itemtype) == '') { $itemtype = 'TICKETSOFFLINE'; }
				}
				$itemtype = strtoupper($itemtype);
			}
			if (trim($itemtype) == '') { $itemtype = 'TICKETSOFFLINE'; }

?>

	<?php

	// PAGINA ID
		// Verificamos la página que se esta navegando
		if (isset($_GET['page'])) {
			
			$pagingcurrentpage = setOnlyNumbers($_GET['page']);
			if (!is_numeric($pagingcurrentpage)) {
				$pagingcurrentpage = 1;
			}
			
		} else {
			// Si no hay página, indicamos que estamos en la primera
			$pagingcurrentpage = 1;
			
		}
		// Navegación páginas
		$next = $pagingcurrentpage + 1;
		$previous = $pagingcurrentpage - 1;
		// Navegación página o URL
		$paginglistpage = str_replace($_SESSION[$configuration['appkey']]['apppath'], '',strtolower($scriptactual));
		$paginglistpage = str_replace(chr(92),"/",$paginglistpage);


	// BUSQUEDA
		$busqueda = "";
		if (isset($_GET['q'])) {
			$busqueda = setOnlyText($_GET['q']);
			echo "Búsqueda: <em>".$busqueda."</em>";
		}


			// PAGING INDEX
				// Variables de control del paginado
				$pagingelements		= 0;
				$pagingpages    	= 0;
				//$pagingcurrentpage  = 0;

				$pagingpagerows		= 10;
				$pagingpagerowsize	= 1;
				$pagingpagerecords	= $pagingpagerowsize * $pagingpagerows;

				// Obtengo el índice del paginado
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDesk".$itemtype."List 
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'index', '0', '".$pagingpagerecords."', '', '', '".$itemtype."';";
				$dbtransactions->query($query);
				$my_row=$dbtransactions->get_row();
				$pagingelements		= $my_row['Rows']; // Total Usuarios
				$pagingpages    	= $my_row['Pages']; // Páginas totales, división de elementos entre pagesize
				//$pagingcurrentpage  = 1;

			// PAGING LIST [ROWS]
				$pagerowindex 	 	 = 1; // 
				$pagerowelements	 = 1; // Elementos por fila
				$pagerowelementindex = 1; // Índice de elementos por fila

			// PAGING CONTROL
				$pagerecordsindex  = 1; 	// Indice contador de elementos
				$pagerowindex  	   = 1; 	// Indice para separador de filas o bloques


?>
  
			<!-- LIST GRID:begin -->   
                <table class="tablelistitems">
                  <thead>
                  <tr>
                    <td width="15%">#</td>
                    <td width="15%">Tarjeta</td>
                    <td width="20%">Cadena</td>
                    <td width="25%">Art&iacute;culo</td>
                    <td width="15%">Ticket</td>
                    <td width="10%">Fecha</td>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
				  
			// PAGING CONTENT OR LIST
				// Obtengo el contenido de la lista
				$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDesk".$itemtype."List
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'list', '".$pagingcurrentpage."', '".$pagingpagerecords."', '', '', '".$itemtype."';";
				$dbtransactions->query($query);
				$elementos = $dbtransactions->count_rows(); 	// Total de elementos
				$pagingelements = $elementos;
	
				// Imprimimos en pantalla cada uno de los parámetros
				while($my_row=$dbtransactions->get_row()){ 
				
						$color = "FFFFFF";
						$bgcolor = "FFFFFF";
						if ($my_row['TicketOfflineStatus'] == 'NEW') { 
							$color = "e74c3c"; 
							$bgcolor = "FFCFCA"; 
						}
						if ($my_row['TicketOfflineStatus'] == 'AUTHORIZED') { 
							$color = "f1c40f"; 
							$bgcolor = "fff6bf"; 
						}

                  ?>
                      <tr>
                        <td style="background-color:#<?php echo $bgcolor; ?>; border-left:4px solid #<?php echo $color;?>;">
                        <a href="?m=helpdesk&s=ticketoffline&a=view&n=<?php echo $my_row['RecordId']; ?>&t=<?php echo strtolower($itemtype); ?>" title="Ver Ticket Offline">
						<span style="font-size:12px;">
						<?php echo $my_row['CaseNumber']; ?>
						</span>
                        </a><br />
						<span style="font-size:8px;">
                        <?php echo $my_row['TicketOfflineStatus']; ?>
						</span>
                        </td>
                        <td>
                        <a href="?m=affiliation&s=items&a=view&q=<?php echo $my_row['CardNumber']; ?>" target="_blank" title="Ver Afiliado">
						<?php echo $my_row['CardNumber']; ?>
                        </a>
                        </td> 
                        <td>
						<?php echo $my_row['ConnectionName']; ?> [<?php echo $my_row['ConnectionId']; ?>]
                        </td>
                        <td>
						<span style="font-size:8px;">
                        <?php echo $my_row['ItemQuantity']; ?> x 
						<?php echo $my_row['ItemSKU']; ?><br />
						<?php echo $my_row['ItemName'].'<br />['.$my_row['ItemBrand'].']'; ?>
						</span>
                        </td>
                        <td>
						<span style="font-size:8px;">
                        <?php echo $my_row['InvoiceNumber']; ?><br />
                        @ <?php echo $my_row['InvoiceDate']; ?>
						</span>
                        </td>
                        <td>
						<?php echo $my_row['RecordDate']; ?>
                        </td>
                      </tr>
                 <?php
                  }
				  if ($pagingelements == 0) {
					  ?>
                          <tr>
                            <td colspan="6" align="center"><span style="font-style:italic;">Sin Tickets</span></td>
                          </tr>
                      
                      <?php
				  }
                  ?>
                  </tbody>
                </table>
			<!-- LIST GRID:end -->            

		<!-- PAGING BUTTONS:begin -->
        <br />
		<div class="pagination">
		<?php

		// Si hay más de una página en el set, mostramos los botones..
		if ($pagingpages > 1) {

				
				// PAGE CALCULATIONS
					// Control de páginas antes y después
						$pagingnextpage 	= $pagingcurrentpage + 1;
						$pagingpreviouspage = $pagingcurrentpage - 1;
						$pagingpagesmax		= 8;
						// Si el total de páginas es menor al rango de 10...
						if ($pagingpagesmax > $pagingpages) { $pagingpagesmax = $pagingpages; }
	
					// Rango de páginas, primera y última
						// Primera página
						$pagingpagesfirst	= $pagingcurrentpage - ($pagingpagesmax/2);
						if ($pagingpagesfirst  < 1) { 
							$pagingpagesfirst = 1;
						}
					
						// Última página
						$pagingpageslast	= ($pagingpagesfirst + $pagingpagesmax) - 1;
						if ($pagingpageslast  > $pagingpages) { 
							$pagingpageslast  = $pagingpages; 
							$pagingpagesfirst = ($pagingpageslast - $pagingpagesmax) + 1;
							if ($pagingpagesfirst  < 1) { $pagingpagesfirst = 1; }
						}
	

				// PAGE INDEX
					echo "P&aacute;gina ".$pagingcurrentpage." de ".$pagingpages;
					echo "<br />";
					echo "<br />";


				// PREVIOUS PAGE
					// Previous, si la página es la primera, lo deshabilitamos
					if ($pagingcurrentpage == 1) { ?>
						<span class='disabled'>&#171; Anterior</span>
					<?php } else { ?>
						<a href="javascript:ajaxListPageBrowse('<?php echo $paginglistpage; ?>', '<?php echo $pagingpreviouspage; ?>');">
                        &#171; Anterior</a>
                    <?php    
					}


				// ALL PAGES
						$pagingpagetoprint = $pagingpages;
						if ($pagingpagetoprint > $pagingpagesmax) { $pagingpagetoprint = $pagingpagesmax; }
						
						// FIRS PAGE
						if ($pagingpagesfirst > 1) { ?>
							<a href="javascript:ajaxListPageBrowse('<?php echo $paginglistpage; ?>', '1');">1</a>
                            &nbsp;...&nbsp;
                        <?php    
						}
	
						
						// Recorrido de las páginas
						$pagetoprint = 0;
						for ($i=0;$i<$pagingpagesmax;$i++) {
							// Calculamos la página a imprimir
							$pagetoprint = $pagingpagesfirst + $i;
							if ($pagetoprint > $pagingpages) { break; }
							// Si la página seleccionada es la actual...
							if ($pagingcurrentpage == $pagetoprint) { ?>
								<span class='current'><?php echo $pagetoprint; ?></span>
							<?php } else { ?>
								<a href="javascript:ajaxListPageBrowse('<?php echo $paginglistpage; ?>', '<?php echo $pagetoprint; ?>');">
								<?php echo $pagetoprint; ?></a>
                            <?php    
							}
							
						}	
	
						// LAST PAGE
						if ($pagingpageslast < $pagingpages) { ?>
							&nbsp;...&nbsp;
                            <a href="javascript:ajaxListPageBrowse('<?php echo $paginglistpage; ?>', '<?php echo $pagingpages; ?>');">
							<?php echo $pagingpages; ?></a>
                        <?php    
						}
	

				// NEXT PAGE
					// Next, si la página es la primera, lo deshabilitamos
					if ($pagingcurrentpage == $pagingpages) { ?>
						<span class='disabled'>Siguiente &#187;</span>
					<?php } else { ?>
						<a href="javascript:ajaxListPageBrowse('<?php echo $paginglistpage; ?>', '<?php echo $pagingnextpage; ?>');">
                        Siguiente &#187;</a>
                    <?php    
					}
				
				echo "<br />";
		} 
		
		?>
		</div>
		<!-- PAGING BUTTONS:end -->        
