<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* affiliation.php
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
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------

?>
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
                    <?php
				
					// Obtengo el índice del paginado
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationIndicators
												'affiliation',
												'".$configuration['appkey']."',
												'".$_SESSION[$configuration['appkey']]['userid']."';";
					$dbconnection->query($query);
					$my_row=$dbconnection->get_row();
					$chartlabels		= $my_row['ChartLabels'];
					$chartvalues    	= $my_row['ChartValues'];
					$chartvaluemax    	= $my_row['ChartMaxValue'];
					$indicador1	    	= $my_row['Indicador1'];
					$indicador2	    	= $my_row['Indicador2'];
					$indicador3	    	= $my_row['Indicador3'];
				
//                    $chartlabels = "";
//                    for ($i=0;$i<14;$i++) {
//                        $chartlabels = subtractDaysFromToday($i)."|".$chartlabels;
//                    }
//                    $chartlabels = substr($chartlabels, 0, -1);
//					$chartlabels = "11 Sep|12 Sep|13 Sep|14 Sep|15 Sep|16 Sep|17 Sep|18 Sep|19 Sep|20 Sep|21 Sep|22 Sep|23 Sep|24 Sep";
//					$chartvalues = "150,103,132,174,156,118,131,78,81,112,142,0,0,0";
//					$chartvaluemax = "200";
                    
                    ?>
                    
                    <img src="https://chart.googleapis.com/chart?chxr=0,0,<?php echo $chartvaluemax; ?>&chxl=1:|<?php echo $chartlabels; ?>|2:|%5B%C3%9Altimas+dos+semanas%5D&chxp=2,100&chxs=1,676767,10,0,l,676767|2,676767,9,1,l,676767&chxt=y,x,x&chs=800x200&cht=lc&chco=0072C6&chds=0,<?php echo $chartvaluemax; ?>&chd=t:<?php echo $chartvalues; ?>&chg=-1,-1,1,1&chls=4,4,0&chm=B,F0F6FB4D,0,0,0|o,FFFFFF,0,-1,13|o,F78F1E,0,-1,10&chtt=Afiliaci%C3%B3n" width="800" height="200" alt="Afiliación" class="googlechart" />
                    <br />
                    <br />
                    <br />
                    
                    <ul id="affiliationtabs" class="shadetabs2">
                    <li><a href="#" class="selected" rel="#default">Afiliados</a></li>
                    <li><a href="affiliation/affiliation_lists.php" rel="tabcontainer">Listas</a></li>
                    </ul>
                    <div id="affiliationdivcontainer" class="shadetabs2divcontainer">
                        Afiliación al programa.
                        <br /><br />
                        
                        <table class="tableresume">
                          <tr>
                            <td>
                            Afiliados<br />
                            <span class="textLarge"><?php echo $indicador1; ?></span><br />
                            <span class="textLight">[Inscritos o afiliados al programa]</span>
                            </td>
                            <td>
                            Activos<br />
                            <span class="textLarge"><?php echo $indicador2; ?></span><br />
                            <span class="textLight">[Tarjetas con actividad reciente]</span>
                            </td>
                            <td>
                            Recompensados<br />
                            <span class="textLarge"><?php echo $indicador3; ?></span><br />
                            <span class="textLight">[Tarjetas con bonificaci&oacute;n]</span>
                            </td>
                          </tr>
                       </table>
                       <br />
                    <table width="100%">
                      <tr>
                        <td align="right">
                        
                      	<form action="index.php" method="get" name="orveefrmhomesearch" onsubmit="return CheckInitSearchRequiredFields()">
                            <input name="m" type="hidden" value="affiliation" />
                            <input name="s" type="hidden" value="items" />
                            <input name="a" type="hidden" value="view" />
                        <input class="inputbusquedatext" id="qsearch" type="text" name="q" value="Buscar afiliado..." onfocus="if(this.value==this.defaultValue) this.value='';" title="Ingrese los datos y pulse ENTER" />
                        </form>
                        </td>
                      </tr>
                    </table>
					<br />
                       
                        <!--<div id="ListPlaceholder" style="height:auto;">-->
                        <div id="ListPlaceholder" style="height:360px;">
                        <?php 
                        require("affiliation/affiliation_items_list.php");
                        ?>
                        </div>
                    
                    </div>
                    
                    <script type="text/javascript">
                    var tabs=new ddajaxtabs("affiliationtabs", "affiliationdivcontainer")
                    tabs.setpersist(true)
                    tabs.setselectedClassTarget("link") //"link" or "linkparent"
                    tabs.init()
                    </script>
            
                    <br /><br />
                    
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
