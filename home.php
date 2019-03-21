<?php
/**
*
* TYPE:
*	INDEX REFERENCE
*
* home.php
* 	Página principal del módulo de administración de afiliados.
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

				<SCRIPT type="text/javascript">
                <!--
                
                    function CheckInitSearchRequiredFields() {
                        var errormessage = new String();
                        var str = document.orveefrminitsearch.q.value;
                        document.orveefrminitsearch.q.value = str.replace(/^\s*|\s*$/g,"");
                        
                        var longitud = document.orveefrminitsearch.q.value.length;
						
						if (document.orveefrminitsearch.q.value == document.orveefrminitsearch.q.defaultValue) {
							document.orveefrminitsearch.q.value = "";
							longitud = 0;
						}
                
                        if ((errormessage.length == 0) && (longitud == 0))
                            { errormessage += "\n- Ingresa los datos del afiliado!."; }
                
                        // Put field checks above this point.
                        if(errormessage.length > 2) {
                            //var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
                            //var contenidofooter = "</p>";
                            alert('Para buscar al afiliado, por favor: ' + errormessage);
                            document.getElementById("qsearch").focus();
                            //document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
                            return false;
                            
                        }
                            
                        document.orveefrminitsearch.submit();
                        return true;
                    } // end of function CheckHomeSearchRequiredFields()
                
                
                //-->
                </SCRIPT>                   
                
                       <table width="100%" border="0" cellspacing="3" align="center">
                          <tr>
                          
                            <td width="50%" align="center" style="padding:20px">
                            
                                <table class="celdahomebuttons">
                                  <tr>
                                    <td height="56" width="56">
                                    <a href="index.php?m=affiliation&s=items&a=new">
                                    <img src="images/imageuseradd.gif" alt="Nueva Afiliación" title="Nueva Afiliación" class="imagenaffiliationuser" />
                                    </a>
                                    </td>
                                    <td style="vertical-align:middle">
                                    <a href="index.php?m=affiliation&s=items&a=new">
                                    Nueva Afiliaci&oacute;n</a><br />
                                    Afiliar una nueva tarjeta al programa.
                                    </td>
                                  </tr>
                                </table>
                            
                            </td>
                            
                            <td width="50%" align="center" style="padding:20px">
                            
                                <table class="celdahomebuttons">
                                  <tr>
                                    <td height="56" width="56">
                                    <img src="images/imageuser.gif" alt="Búsqueda Afiliado" title="Búsqueda Afiliado" class="imagenaffiliationuser" />
                                    </td>
                                    <td height="56">
                                        <form action="index.php" method="get" name="orveefrminitsearch" onsubmit="return CheckInitSearchRequiredFields()">
                                            <input name="m" type="hidden" value="affiliation" />
                                            <input name="s" type="hidden" value="items" />
                                            <input name="a" type="hidden" value="view" />
                                            <input name="q" id="qsearch" type="text" class="inputtextrequired" value="Buscar afiliado..." onfocus="if(this.value==this.defaultValue) this.value='';" size="30" />&nbsp;
                                            <input name="Buscar" type="submit" value="Buscar" />
                                        </form><br />
                                        Ingrese el n&uacute;mero de afiliado a buscar.
                                    </td>
                                  </tr>
                                </table>
                
                            </td>
                            
                          </tr>
                        </table>
                    <br />
        
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

