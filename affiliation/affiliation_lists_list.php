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
if(session_id() == '') {
    session_start();
}
			
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
	

// --------------------
// INICIO CONTENIDO
// --------------------

	// CURRENT PAGE SCRIPT
		$listscriptparts = explode(chr(92), $scriptactual);
		$listscript = $listscriptparts[count($listscriptparts)-1];
		$listpageparameters = '';

	// MODULE script assembly
		$listmodule = "";
		$listpageparts = explode("_", $listscript);
		$listmodule = $listpageparts[0];

		// NAVIGATION LOG
		//setNavigationLog('navigation', 0, $module.'/'.getCurrentPageScript());
		setNavigationLog('navigation', 0, $listmodule.'/'.$listscript);


		// ItemType EMAIL or SMS
			if (isset($itemtype)) {
				if (trim($itemtype) == '') { $itemtype = 'lists'; }
			} else {
				$itemtype = 'lists';
				if (isset($_GET['t'])) {
					$itemtype = setOnlyLetters($_GET['t']);
					if (trim($itemtype) == '') { $itemtype = 'lists'; }
				}
				$itemtype = strtolower($itemtype);
			}
			if (trim($itemtype) == '') { $itemtype = 'lists'; }

	// PAGE PARAMETERS ... if required		
			$listpageparameters = '';
			if (isset($itemtype)) {
				$listpageparameters = 't='.$itemtype;
			} 
			
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
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsList
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'index', '0', '".$pagingpagerecords."', '','';";
				$dbconnection->query($query);
				$my_row=$dbconnection->get_row();
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
                    <table width="100%">
                      <tr>
                        <td align="left">
							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
									$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>                               
									<table >
									  <tr>
										<td class="botonstandard"><img src="images/bulletnew.png" />&nbsp;<a href="?m=affiliation&s=lists&a=new" target="_blank">Nueva Lista</a></td>
									  </tr>
									</table>     
							<?php } else { ?>     
									&nbsp;                                              
							<?php } ?>                                                   
                         </td>
                        <td align="right">
							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
									$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>                               
                        
									<form action="index.php" method="get" name="orveefrmaffiliationlistsearch" onsubmit="return CheckInitSearchRequiredFields()">
										<input name="m" type="hidden" value="affiliation" />
										<input name="s" type="hidden" value="lists" />
										<input name="a" type="hidden" value="view" />
									<input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar lista..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
									</form>
							<?php } else { ?>     
									&nbsp;                                              
							<?php } ?>                                                   
                         </td>
                        </td>
                      </tr>
                    </table>
					<br />

			<!-- LIST GRID:begin -->            
                <table class="tablelistitems">
                  <thead>
                  <tr>
                    <td width="5%">&nbsp;</td>
                    <td width="40%">Lista</td>
                    <td width="25%">Alta</td>
                    <td width="15%">Status</td>
                    <td width="15%">&nbsp;</td>
                  </tr>
                  </thead>
                  <tbody>

                  <?php 
				  
			// PAGING CONTENT OR LIST
				// Obtengo el contenido de la lista
				$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsList
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'list', '".$pagingcurrentpage."', '".$pagingpagerecords."', '','';";
				$dbconnection->query($query);
				$elementos = $dbconnection->count_rows(); 	// Total de elementos
				$pagingelements = $elementos;
	
				// Imprimimos en pantalla cada uno de los parámetros
				while($my_row=$dbconnection->get_row()){ 
				
						// listid
						$listid = $my_row['ListId'];

						// liststatusimage
						$liststatusimage = 'bulletcolourgraylight.png';
						if ($my_row['ListUsage'] > 0)
							{ $liststatusimage = 'bulletcolourgreen.png'; }

                  ?>
                  
                      <tr>
                        <td>&nbsp;</td>
                        <td>
                            <a href="?m=affiliation&s=lists&a=view&n=<?php echo $listid; ?>">
                            <span style="font-size:10px;">
                            <?php echo $listid; ?>.&nbsp;
                            </span>
                            <span style="font-size:12px;">
                            <?php echo urldecode($my_row['ListName']); ?>
                            </span>
                            </a><br />
                            <span style="font-size:9px;font-style:italic;">
                            <?php echo urldecode($my_row['ListDescription']); ?>
                            </span>
                        </td> 
                        <td><?php echo $my_row['ListDate']; ?></td>
                        <td><img src="images/<?php echo $liststatusimage; ?>" alt="<?php echo $my_row['ListUsage']; ?>" /></td>
                        <td>
                        <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                        	<a href="?m=reports&s=items&a=download&t=list&n=<?php echo $listid; ?>&fn=<?php echo urlencode($my_row['ListName']); ?>&ft=csv" target="_blank" title="Descargar Lista"><img src="images/bulletdownload.png" /></a>
                        <?php } else { ?>
                        	&nbsp;
                        <?php } ?>
                        </td> 
                      </tr>

                 <?php
                  }
 				  if ($pagingelements == 0) {
					  ?>
                          <tr>
                            <td colspan="5" align="center"><span style="font-style:italic;">Sin Listas</span></td>
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
