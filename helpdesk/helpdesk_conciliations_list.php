<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* interactions_x.php
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

// CONTAINER & IFRAME CHECK
	// Si el llamado no viene del index o contenedor principal ...PAGE NOT FOUND
	// Si el llamado no viene de una p�gina dentro del mismo dominio ...PAGE NOT FOUND
	if (!isset($_SERVER['HTTP_REFERER'])) {
		if (!isset($appcontainer)) { 
			//header("HTTP/1.0 404 Not Found");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			exit();
		}
	} else {

		// INCLUDES & REQUIRES
			if (!isset($appcontainer)) {
				include_once('../includes/configuration.php');	// Archivo de configuraci�n
				include_once('../includes/functions.php');	// Librer�a de funciones
			}
	
		// REQUEST SOURCE VALIDATION
			$requestsource = getRequestSource();
			if ($requestsource !== 'domain' && $requestsource !== 'page') {
				$actionerrorid = 10;
				require_once('../loginwarningtab.php');
				exit();
			}

	}
	
		// Verificamos la p�gina que se esta navegando
		if (!isset($appcontainer)) {
			
			// INIT
				// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// INCLUDES & REQUIRES
				include_once('../includes/configuration.php');	// Archivo de configuraci�n
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexi�n a base de datos
				include_once('../includes/functions.php');	// Librer�a de funciones

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
				if (trim($itemtype) == '') { $itemtype = 'CONCILIATIONS'; }
			} else {
				$itemtype = 'CONCILIATIONS';
				if (isset($_GET['t'])) {
					$itemtype = setOnlyLetters($_GET['t']);
					if (trim($itemtype) == '') { $itemtype = 'CONCILIATIONS'; }
				}
				$itemtype = strtoupper($itemtype);
			}
			if (trim($itemtype) == '') { $itemtype = 'CONCILIATIONS'; }

?>

	<?php

	// PAGINA ID
		// Verificamos la p�gina que se esta navegando
		if (isset($_GET['page'])) {
			
			$pagingcurrentpage = setOnlyNumbers($_GET['page']);
			if (!is_numeric($pagingcurrentpage)) {
				$pagingcurrentpage = 1;
			}
			
		} else {
			// Si no hay p�gina, indicamos que estamos en la primera
			$pagingcurrentpage = 1;
			
		}
		// Navegaci�n p�ginas
		$next = $pagingcurrentpage + 1;
		$previous = $pagingcurrentpage - 1;
		// Navegaci�n p�gina o URL
		$paginglistpage = str_replace($_SESSION[$configuration['appkey']]['apppath'], '',strtolower($scriptactual));
		$paginglistpage = str_replace(chr(92),"/",$paginglistpage);


	// BUSQUEDA
		$busqueda = "";
		if (isset($_GET['q'])) {
			$busqueda = setOnlyText($_GET['q']);
			echo "B�squeda: <em>".$busqueda."</em>";
		}


			// PAGING INDEX
				// Variables de control del paginado
				$pagingelements		= 0;
				$pagingpages    	= 0;
				//$pagingcurrentpage  = 0;

				$pagingpagerows		= 10;
				$pagingpagerowsize	= 1;
				$pagingpagerecords	= $pagingpagerowsize * $pagingpagerows;
                $indicador1         = '';
                $indicador2         = '';
                $indicador3         = '';

				// Obtengo el �ndice del paginado
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_HelpDesk".$itemtype."List 
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'index', '0', '".$pagingpagerecords."', '', '', '".$itemtype."';";
				$dbtransactions->query($query);
				$my_row=$dbtransactions->get_row();
				$pagingelements		= $my_row['Rows']; // Total Usuarios
				$pagingpages    	= $my_row['Pages']; // P�ginas totales, divisi�n de elementos entre pagesize
				//$pagingcurrentpage  = 1;
                $indicador1 = $my_row['Indicador1'];
                $indicador2 = $my_row['Indicador2'];
                $indicador3 = $my_row['Indicador3'];

			// PAGING LIST [ROWS]
				$pagerowindex 	 	 = 1; // 
				$pagerowelements	 = 1; // Elementos por fila
				$pagerowelementindex = 1; // �ndice de elementos por fila

			// PAGING CONTROL
				$pagerecordsindex  = 1; 	// Indice contador de elementos
				$pagerowindex  	   = 1; 	// Indice para separador de filas o bloques


?>
<SCRIPT type="text/javascript">
<!--

	function CheckSideSearchRequiredFields() {
		var errormessage = new String();
		
		var str = document.orveefrmsearch.q.value;
		document.orveefrmsearch.q.value = str.replace(/^\s*|\s*$/g,"");
		
		var longitud = document.orveefrmsearch.q.value.length;

		//if (isNaN(document.orveefrmsearch.q.value))
		//	{ errormessage += "\n- Ingresa un n�mero de tarjeta a buscar!."; }

		//if ((errormessage.length == 0) && (longitud != 13))
		//	{ errormessage += "\n- Ingresa un n�mero de tarjeta v�lido!."; }

		if ((errormessage.length == 0) && (longitud == 0))
			{ errormessage += "\n- Ingresa los datos de la conexi�n!."; }

		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para buscar la conexi�n, por favor: ' + errormessage);
			document.getElementById("qsearch").focus();
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			return false;
			
		}
			
		document.orveefrmsearch.submit();
		return true;
	} // end of function CheckHomeSearchRequiredFields()


//-->
</SCRIPT>    
			<!-- LIST GRID:begin -->   
                <table class="tablelist">
                  <thead>
                  <tr>
                    <td width="10%">ItemOwnerId</td>
                    <td width="30%">ItemOwner</td>
                    <td width="20%"><?php echo $indicador1; ?></td>
                    <td width="20%"><?php echo $my_row['Indicador2']; ?></td>
                    <td width="20%"><?php echo $my_row['Indicador3']; ?></td>
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
	
				// Imprimimos en pantalla cada uno de los par�metros
				while($my_row=$dbtransactions->get_row()){ 

                  ?>
                      <tr>
                        <td>
                        <a href="?m=helpdesk&s=<?php echo strtolower($itemtype); ?>&a=view&n=<?php echo $my_row['ItemOwnerId']; ?>&t=<?php echo strtolower($itemtype); ?>">
                        <span style="font-size:11px;">
						<?php echo $my_row['ItemOwnerId']; ?>
                        </span>
                        </a>
                        </td> 
                        <td>
						<?php echo $my_row['ItemOwner']; ?>
                        </td>
                        <td>
                            <a href="<?php echo $my_row['Indicador4']; ?>"><span style="font-size:10px;">Bonus</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                            <?php
                                if($my_row['Sustentos1'] != ''){
                            ?>
                                    <a href="<?php echo $my_row['Sustentos1']; ?>"><span style="font-size:10px;">Sustentos</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                            <?php
                                }
                            ?>
                        </td>
                        <td>
                        <span style="font-size:9px;">
						    <a href="<?php echo $my_row['Indicador5']; ?>"><span style="font-size:10px;">Bonus</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                            <?php
                                if($my_row['Sustentos2'] != ''){
                            ?>
                                    <a href="<?php echo $my_row['Sustentos2']; ?>"><span style="font-size:10px;">Sustentos</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                            <?php
                                }
                            ?>
                        </span>
                        </td>
                        <td>
                        <span style="font-size:9px;">
                            <a href="<?php echo $my_row['Indicador6']; ?>"><span style="font-size:10px;">Bonus</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                            <?php
                                if($my_row['Sustentos3'] != ''){
                            ?>
                            <a href="<?php echo $my_row['Sustentos3']; ?>"><span style="font-size:10px;">Sustentos</span><img src="images/bulletdown.png" width="16" height="16" /></a>
                             <?php
                                }
                            ?>
                        </span>
                        </td>
                      </tr>
                 <?php
                  }
				  if ($pagingelements == 0) {
					  ?>
                          <tr>
                            <td colspan="5" align="center"><span style="font-style:italic;">Sin ItemOwners</span></td>
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

		// Si hay m�s de una p�gina en el set, mostramos los botones..
		if ($pagingpages > 1) {

				
				// PAGE CALCULATIONS
					// Control de p�ginas antes y despu�s
						$pagingnextpage 	= $pagingcurrentpage + 1;
						$pagingpreviouspage = $pagingcurrentpage - 1;
						$pagingpagesmax		= 8;
						// Si el total de p�ginas es menor al rango de 10...
						if ($pagingpagesmax > $pagingpages) { $pagingpagesmax = $pagingpages; }
	
					// Rango de p�ginas, primera y �ltima
						// Primera p�gina
						$pagingpagesfirst	= $pagingcurrentpage - ($pagingpagesmax/2);
						if ($pagingpagesfirst  < 1) { 
							$pagingpagesfirst = 1;
						}
					
						// �ltima p�gina
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
					// Previous, si la p�gina es la primera, lo deshabilitamos
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
	
						
						// Recorrido de las p�ginas
						$pagetoprint = 0;
						for ($i=0;$i<$pagingpagesmax;$i++) {
							// Calculamos la p�gina a imprimir
							$pagetoprint = $pagingpagesfirst + $i;
							if ($pagetoprint > $pagingpages) { break; }
							// Si la p�gina seleccionada es la actual...
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
					// Next, si la p�gina es la primera, lo deshabilitamos
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
