<?php
/**
*
* TYPE:
*	IFRAME REFERENCE
*
* security_users_list.php
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
		setNavigationLog('navigation', 0, 'security/'.getCurrentPageScript());
		
		
// --------------------
// INICIO CONTENIDO
// --------------------


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
			echo "B&uacute;squeda: <em>".$busqueda."</em>";
		}


			// PAGING INDEX
				// Variables de control del paginado
				$pagingelements		= 0;
				$pagingpages    	= 0;
				//$pagingcurrentpage  = 0;

				$pagingpagerows		= 3;
				$pagingpagerowsize	= 3;
				$pagingpagerecords	= $pagingpagerowsize * $pagingpagerows;

				// Obtengo el índice del paginado
				$query  = "EXEC dbo.usp_app_SecurityUsersList 
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'index', '0', '".$pagingpagerecords."', '', '', '';";
				$dbsecurity->query($query);
				$my_row=$dbsecurity->get_row();
				$pagingelements		= $my_row['Rows']; // Total Usuarios
				$pagingpages    	= $my_row['Pages']; // Páginas totales, división de elementos entre pagesize
				//$pagingcurrentpage  = 1;

			// PAGING LIST [ROWS]
				$pagerowindex 	 	 = 1; // 
				$pagerowelements	 = 3; // Elementos por fila
				$pagerowelementindex = 1; // Índice de elementos por fila

			// PAGING CONTROL
				$pagerecordsindex  = 1; 	// Indice contador de elementos
				$pagerowindex  	   = 1; 	// Indice para separador de filas o bloques

			?>

			<!-- LIST GRID:begin -->            
			<table class="gridsecurityusers">
            <?php

			// PAGING CONTENT OR LIST
				// Obtengo el contenido de la lista
				$query  = " EXEC dbo.usp_app_SecurityUsersList
								'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
								'list', '".$pagingcurrentpage."', '".$pagingpagerecords."', '', '', '';";
				$dbsecurity->query($query);
						
				// Imprimimos en pantalla cada uno de los parámetros
				while($my_row=$dbsecurity->get_row()){ 
					
						// Si rebasamos el límite del bloque, reiniciamos a 1
						if ($pagerowindex == $pagerowelements+1) { $pagerowindex = 1; }	
						// Inicio del bloque o tabla
						if ($pagerowindex == 1) { 
							echo '<tr>'.chr(13).chr(10);
						}

						// Imagen en el output
						$icono = "images/iconuser.gif";
						if ($my_row['UserStatusId'] == 1)  { $icono = "images/iconuseractive.gif"; }
						if ($my_row['UserStatusId'] == 3)  { $icono = "images/iconuserwarning.gif"; }
						if ($my_row['UserStatusId'] == 6)  { $icono = "images/iconuserinactive.gif"; }
						
						if ($my_row['UserProfileId'] == 1) { $icono = "images/iconuseradmin.gif"; }
						if ($my_row['UserProfileId'] == 2) { $icono = "images/iconuseradmin.gif"; }	
						
						// Cuerpo del bloque
						?>
						<td width="33%">

							<!-- USER CELDA:begin-->
                            <table class="celdasecurityuser">
                              <tr>
                                <td width="32">
                                <img src="<?php echo $icono; ?>" alt="User Status" title="User Status" class="imagensecurityuser" />
                                </td>
                                <td align="left">
                                <a href="?m=security&s=users&a=view&n=<?php echo $my_row['UserId']; ?>">
								<?php echo $my_row['Username']; ?></a><br />
                                <?php echo $my_row['Name']." ".$my_row['LastName']; ?><br />
                                <?php echo $my_row['Email']; ?><br />
                                <?php echo $my_row['UserProfile']; ?><br />
                                <?php echo $my_row['UserStatus']; ?><br />
                                &Uacute;ltimo Acceso: <em><?php echo $my_row['UserLastAccess']; ?></em>
                                </td>
                                <td align="center" width="18">
                                <span style="font-size:18px;text-align:center;">
                                <a href="?m=security&s=users&a=view&n=<?php echo $my_row['UserId']; ?>">
                                <span style="font-size:18px;text-decoration:underline">
								<?php echo (($pagingcurrentpage-1)*$pagingpagerecords)+$pagerecordsindex; ?></span></a><br>

                                <?php if ($my_row['UserStatusId'] <> 6) { ?>
                                <?php if ($my_row['UserStatusId'] == 3) { ?>
                                	<a href="?m=security&s=users&a=unblock&n=<?php echo $my_row['UserId']; ?>"><img src="images/bulletcheck.png" alt="Desbloquear" title="Desbloquear" /></a><br>
                                <?php } else { ?>
                                	<a href="?m=security&s=users&a=block&n=<?php echo $my_row['UserId']; ?>"><img src="images/bulletblock.png" alt="Bloquear" title="Bloquear" /></a><br>
                                <?php } ?>
                                <a href="?m=security&s=users&a=delete&n=<?php echo $my_row['UserId']; ?>"><img src="images/bulletdelete.png" alt="Eliminar" title="Eliminar" /></a>
                                <?php } ?>
                                
                                </span>
                                </td>
                              </tr>
                            </table>
							<!-- USER CELDA:end-->

						 </td>
						<?php
								
						// Fin del bloque o tabla
						if ($pagerowindex == $pagerowelements) { 
							echo '</tr>'.chr(13).chr(10);
						}
		
						// Indices y contadores
						$pagerowindex = $pagerowindex + 1;
						$pagerecordsindex = $pagerecordsindex + 1;
						
				}
				
				$pagerecordsindex = $pagerecordsindex - 1; // Quitamos el último porque sobra
				$pagingelements = $pagerecordsindex;
	
				// Verifico si el total cuadra con el número de elementos definidos para el bloque (3), sino lo ajustamos
				$pagelastrowmissing = 0;
				if ($pagingelements % $pagerowelements > 0) { 
					$pagelastrowmissing = $pagerowelements - ($pagingelements % $pagerowelements); 
				
						// Celdas faltantes						
						for ($i = 0; $i < $pagelastrowmissing; $i++) {
							$icono = "images/iconuseradd.gif";
							?>
							<td>
								<table class="celdasecurityuser">
								  <tr>
									<td width="32">
									<img src="<?php echo $icono; ?>" alt="Activo" title="Activo" class="imagensecurityuser" />
									</td>
									<td align="left"><a href="?m=security&s=users&a=new">Nuevo Usuario</a><br />
										&nbsp;<br />
										&nbsp;<br />
										&nbsp;<br />
										&nbsp;<br />
										&nbsp;
									</td>
									<td align="center" width="18">&nbsp;
									
									</td>
								  </tr>
								</table>
							 </td>
							<?php
						}
						echo '</tr>'.chr(13).chr(10);

				}

			?>                    
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
