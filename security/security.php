<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* security.php
* 	Página principal del módulo de seguridad y administración de usuarios.
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

// REFERER
	// Identificamos de donde viene...
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

            <table width="100%">
              <tr>
                <td>
                
                <!-- SECURITY USERS: begin -->
                        <br />
                        <table class="modulesectiontitle">
                            <tr>
                            <td>Usuarios</td>
                            </tr>
                        </table>
                        <br />
                        
                      <table width="100%">
                      <tr>
                        <td align="right">
                        <!--<form action="javascript:ajaxListPageSearch('security/security_users_list.php', '1');" method="get" name="frmbusquedaside">-->
                        <form action="index.php" method="get" name="frmbusquedaside">
                        <input name="m" type="hidden" value="security" />
                        <input name="s" type="hidden" value="users" />
                        <input name="a" type="hidden" value="view" />
                        <input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar usuario..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                        </form>
                        </td>
                      </tr>
                    </table>
                    <br />
                    
                        <div id="ListPlaceholder" style="height:auto;">
                        <?php 
                        require("security/security_users_list.php");
                        ?>
                        </div>


                <!-- SECURITY USERS: end -->


                </td>
              </tr>
              <tr>
                <td>
                
                <!-- SECURITY CONFIGURATION: begin -->
	                <br />
                    <table class="modulesectiontitle">
                        <tr>
                        <td>Configuraci&oacute;n</td>
                        </tr>
                    </table>
                    <br />
                    <?php
					
					$indice = 1; 	// Indice contador de elementos
					$filas  = 1; 	// Indice para separador de filas o bloques
					
					// Obtengo los parámetros de seguridad
					$query  = " EXEC dbo.usp_app_ParametersList 'Security','';";
					$dbsecurity->query($query);
					
					// Imprimimos en pantalla cada uno de los parámetros
					while($my_row=$dbsecurity->get_row()){ 
					
							if ($filas == 4) { $filas = 1; }	// Si rebasamos el límite del bloque, reiniciamos a 1
							
							// Inicio del bloque o tabla
							if ($filas == 1) { 
								echo '<table class="tableresume"><tr>'.chr(13).chr(10);
							}
	
							// Cuerpo del bloque
							?>
								<td>
								<span class="textLarge">
								<!--<a href="?m=security&s=config&a=edit&n=<?php echo $my_row['ParameterId']; ?>">-->
								<a href="#">
								<?php 
									// Dependiendo del valor en el parámetro, imprimos los valores...
									switch (trim($my_row['ParameterValue'])) {
										case 'True':
											echo '<img src="images/iconsecurityfirewallon.png" width="32" height="32" alt="Activo" />';
											break;
										case 'False':
											echo '<img src="images/iconsecurityfirewalloff.png" width="32" height="32" alt="Inactivo" />';
											break;
										case '':
											echo '<img src="images/iconsecurityquestion.png" width="32" height="32" alt="Sin Definir" />';
											break;
										default:
											echo $my_row['ParameterValue'];
											break;
									}				
								 ?></a></span><br />
								<strong><?php echo $my_row['ParameterName']; ?></strong><br />
								<span class="textLight">[<?php echo $my_row['ParameterDescription']; ?>]</span>
								</td>
							<?php
							
							// Fin del bloque o tabla
							if ($filas == 3) { 
								echo '</tr></table>'.chr(13).chr(10).'<br />'.chr(13).chr(10);
							}
	
							// Indices y contadores
							$filas = $filas + 1;
							$indice = $indice + 1;
					}

					$indice = $indice - 1;
					$elementos = $indice;

					// Verifico si el total cuadra con el número de elementos definidos para el bloque (3), sino lo ajustamos
					$faltantes = 0;
					if ($elementos % 3 > 0) { 
						$faltantes = 3 - ($elementos % 3); 
						
								for ($i = 0; $i < $faltantes; $i++) {
									?>
										<td>
										<span class="textLarge">
										<a href="#">
										&nbsp;
										</a></span><br />
										<strong>&nbsp;</strong><br />
										<span class="textLight">&nbsp;</span>
										</td>
									<?php
								}
								echo '</tr></table>'.chr(13).chr(10).'<br />'.chr(13).chr(10);
						
					}

					?>
                    <br />

                <!-- SECURITY CONFIGURATION: end -->

                </td>
              </tr>
            </table>
            

        <br />
        <br />
        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">

					<!-- Incluimos el sidebar del modulo-->
                    <?php 

					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = str_replace(".php", "_sidebar.php", $modulepage);

					// Verificamos si existe el archivo
					if (file_exists($sidebarfile)) { 
						
						// Incluimos la barra lateral
						include_once($sidebarfile); 
						
					} else { 
					
						// Si no hay barra, activamos la default
						$sidebarfile = "home_sidebar.php";
						include_once($sidebarfile); 
						
					} 	
					
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

