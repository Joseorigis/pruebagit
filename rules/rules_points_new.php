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
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutSelectionValue(document.orveefrmrule.equivalence))
			{ errormessage += "\n- Selecciona una equivalencia!."; }
		if(WithoutContent(document.orveefrmrule.rulename.value))
			{ errormessage += "\n- Ingresa un nombre para la regla!."; }
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar la regla, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}
		//document.orveefrmuser.submit();
		return true;
	} // end of function CheckRequiredFields()


//-->
</SCRIPT>

<script type="text/JavaScript">

var peticion = false;
   var  testPasado = false;
   try {
     peticion = new XMLHttpRequest();
     } catch (trymicrosoft) {

   try {
   peticion = new ActiveXObject("Msxml2.XMLHTTP");
   } catch (othermicrosoft) {
  try {
  peticion = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (failed) {
  peticion = false;
  }
  }
  }
  if (!peticion)
  alert("ERROR AL INICIALIZAR!");

     function getItemCount(urlscript, htmlelement) {
		 
		   var itemtype1 =  document.getElementById('itemtype1').value;
		   var itemtype2 =  document.getElementById('itemtype2').value;
		 
		   var element =  document.getElementById(htmlelement);
		   
		   if(urlscript.indexOf('?') != -1) {
			   var fragment_url = urlscript+'&ItemType1='+itemtype1+'&ItemType2='+itemtype2;
		   }else{
			   var fragment_url = urlscript+'?ItemType1='+itemtype1+'&ItemType2='+itemtype2;
		   }
		   
		   element.innerHTML = '<i>Consultando...</i>';
		   peticion.open("GET", fragment_url);
		   peticion.onreadystatechange = function() {
			   if (peticion.readyState == 4) {
					element.innerHTML = peticion.responseText;
			   }
		   }
		  peticion.send(null);
   }

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

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="points" />
                <input name="a" type="hidden" value="add" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="ruletype" type="hidden" value="ordinary" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Nueva Regla Puntos
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culos<br />

                            <table border="0" cellspacing="0" cellpadding="10" style="padding-left:10px;border-left: 1px solid #ADB1BD;border-right: 1px solid #ADB1BD; background-color: #F0F0F0;">
                              <tr>
                                <td>
                                Clasificaci&oacute;n 1<br />
                                <select name="itemtype1" id="itemtype1" class="selectbasic" onChange="javascript:getItemCount('rules/rules_points_itemscount.php', 'DivItemsCount');" >
                                    <option value="">[Todos]</option>
                                    <?php
                                        $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 'DemoClasificacion1','';";
                                        $dbtransactions->query($query);
                                        while($my_row=$dbtransactions->get_row()){ 
                                            if ($my_row['ItemIsSelected'] == 1) {
                                                echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                                echo "&nbsp;".$my_row['Item']."</option>";
                                            } else {
                                                echo "<option value='".$my_row['ItemId']."'>";
                                                echo "&nbsp;".$my_row['Item']."</option>";
                                            }
                                        }
                                    ?>
                                </select><br />
                                <span class="textHint"> &middot; Marcas o clasificaci&oacute;n.</span>
                                </td>
                                <td rowspan="2" align="right" valign="bottom">
                                <div id="DivItemsCount">
                                <span style="font-size:36px;font-weight:bold;">
                                0
                                </span>
                                </div>                                
                                Art&iacute;culos<br />
                                </td>
                                <td rowspan="5" align="right" valign="bottom">&nbsp;
                                </td>
                              </tr>
                              <tr>
                                <td>
                                Clasificaci&oacute;n 2<br />
                                <select name="itemtype2" id="itemtype2" class="selectbasic" onChange="javascript:getItemCount('rules/rules_points_itemscount.php', 'DivItemsCount');" style="font-size:9px;" >
                                    <option value="">[Todos]</option>
                                    <?php
                                        $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 'DemoClasificacion2','';";
                                        $dbtransactions->query($query);
                                        while($my_row=$dbtransactions->get_row()){ 
                                            if ($my_row['ItemIsSelected'] == 1) {
                                                echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                                echo "&nbsp;".$my_row['Item']."</option>";
                                            } else {
                                                echo "<option value='".$my_row['ItemId']."'>";
                                                echo "&nbsp;".$my_row['Item']."</option>";
                                            }
                                        }
                                    ?>
                                </select><br />
                                <span class="textHint"> &middot; Detalle o clasificaci&oacute;n.</span>
                                </td>
                              </tr>
                            </table>
                            
                    <span class="textHint"> &middot; Art&iacute;culos a incluir en la regla.</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Sucursal<br />
                    <select name="store" id="store" class="selectrequired">
	                    <option value="0">[Todas]</option>
						<?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 
														'ConnectionStores', '',
														'".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."';";
                            $dbtransactions->query($query);
                            while($my_row=$dbtransactions->get_row()){ 
                                if ($my_row['ItemIsSelected'] == 1) {
                                    echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                } else {
                                    echo "<option value='".$my_row['ItemId']."'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                }
                            }
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Sucursal o tienda para aplicar al regla.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Equivalencia<br />
                    <select name="equivalence" id="equivalence" class="selectrequired">
	                    <option value="">[Selecciona un Factor]</option>
						<?php
							for ($i=0;$i<101;$i++) {
                                    echo "<option value='".$i."'>";
									echo "".$i."%</option>";
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Factor de acumulaci&oacute;n en puntos.<br />
                    &middot; Porcentaje de conversi&oacute;n de dinero a puntos.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      Vigencia Inicio<br/>
                      <div><input type="text" name="ruleactivation" id="ruleactivation" value="<?php echo date('d/m/Y'); ?>" class="inputtextrequired" /></div>
			            <span class="textHint">
                    &middot; Fecha programada para inicio de la vigencia.</span></td>
                  </tr>
                  <tr>
                    <td>
                      Vigencia Fin<br/>
                      <div><input type="text" name="ruleexpiration" id="ruleexpiration" value="<?php echo "31/12/2019"; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para fin o termino de la vigencia.</span></td>
                  </tr>

                  <tr>
                    <td>
                      C&oacute;digo<br/>
                    <input name="rulecode" id="rulecode" type="text" class="inputtext" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia para la nueva regla.
                    </span></td>
                  </tr>
                  
                  <tr>
                    <td>
                      Nombre<br/>
                    <input name="rulename" id="rulename" type="text" class="inputtextrequired" size="50" /><br />
                    <span class="textHint">
                    &middot; Nombre para la nueva regla.
                    </span></td>
                  </tr>
                  
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                
			<script language="javascript" type="text/javascript">
                getItemCount('rules/rules_points_itemscount.php', 'DivItemsCount'); 
            </script>
                
			<script type="text/javascript">
                var today = new Date();

                var dd = today.getDate();
                var mm = today.getMonth()+1; //January is 0!
                var yyyy = today.getFullYear();
                
                if(dd<10){dd='0'+dd}
                if(mm<10){mm='0'+mm} 
                today = dd+'/'+mm+'/'+yyyy;
            
                //http://jdpicker.paulds.fr/?p=demo
                $(document).ready(function(){
                    $('#ruleactivation').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
                    });
                    $('#ruleexpiration').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
                    });
            
                });
            </script>                
                
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

