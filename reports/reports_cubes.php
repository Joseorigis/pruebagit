<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
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
		header('Content-Type: text/html; charset=UTF-8');
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

// REFERER
	// Identificamos de donde viene...
	$referer = "";
	if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
	$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
	if ($referer == "") { $referer = "index.php"; }


// --------------------
// INICIO CONTENIDO
// --------------------

	
	// CUBES OR POWERBI
				// CONNECTION
					$connectionkeyword 	= 'PowerBI';
					$connectionname 	= '';
					$connectionlogo 	= '';
					$connectionurl 		= '';
					$connectionkey 		= '';
					$connectionusername = '';
					$connectionpassword = '';
					
					// Get Connection Data
					$elements = 0;
					$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."', 
										'connectiondata', 
										'crm', 
										'0', 
										'Connection', 
										'*', 
										'".$connectionkeyword."', 
										'".$connectionkeyword."';";
					$dbconnection->query($query);
					while($my_row=$dbconnection->get_row()){
							$elements = $elements + 1;
							if ($my_row['ParameterName'] == 'Title') 	{ $connectionname 		= $my_row['ParameterValue'];	}					
							if ($my_row['ParameterName'] == 'Logo') 	{ $connectionlogo 		= $my_row['ParameterValue'];	}					
							if ($my_row['ParameterName'] == 'URL') 		{ $connectionurl  		= $my_row['ParameterValue'];	}					
							if ($my_row['ParameterName'] == 'Key') 		{ $connectionkey  		= $my_row['ParameterValue'];	}					
							if ($my_row['ParameterName'] == 'Username') { $connectionusername 	= $my_row['ParameterValue'];	}					
							if ($my_row['ParameterName'] == 'Password') { $connectionpassword 	= $my_row['ParameterValue'];	}					
					}
					if ($connectionname == '') { $elements = 0; }

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

			<!-- LIST GRID:begin -->            
                <table class="tablelist">
                  <thead>
                  <tr>
                    <td colspan="3">Cubos & BI</td>
                  </tr>
                  </thead>
                  <tbody>

				<?php if ($elements > 0) { ?>
                  
                      <tr>
                        <td width="20%">
                        <a href="<?php echo $connectionurl; ?>" target="_blank" title="Ir a <?php echo $connectionname; ?>">
                        <img src="<?php echo $connectionlogo; ?>" alt="<?php echo $connectionname; ?>" />
                        </a>
                        </td>
                        <td align="left" width="80%">
                        Para consultar Cubos & BI, ingresa a la plataforma de BI<br />
						con la siguiente informaci&oacute;n:<br />
						<br />
						<br />
                        Username: <span style="font-weight:bold;"><?php echo $connectionusername; ?></span><br />
                        Password: <span style="font-weight:bold;"><?php echo $connectionpassword; ?></span><br />
						<br />
						<br />
                        <a href="<?php echo $connectionurl; ?>" target="_blank" title="Ir a <?php echo $connectionname; ?>">Ingresar</a>
						<br />
						<br />
						<br />
                       <a href="orbis_powerbi_datadictionary.xlsx" target="_blank" title="Diccionario Datos & Variables">
                         <span style="font-size:8px;">
                       Ver Diccionario Datos
                        </span>                      
                       </a>
                        </td> 
                      </tr>
                      
				<?php } else { ?>

                      <tr>
                        <td colspan="2" align="center">
                        <span style="font-style:italic;">Sin Conexi&oacute;n</span>
                        </td> 
                      </tr>
                
				<?php } ?>
                  </tbody>
                </table>
			<!-- LIST GRID:end -->     
            
        <br />
        <br />
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

