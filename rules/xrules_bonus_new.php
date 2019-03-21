<?php 
// ***************************************************************************************
// TBD: 1. Al finalizar, quieres activar una alarma?.
// Comparar fechas!!!
// Encadenar combo de itemtype1 con itembonus
// Uso o no el de itemowner?, o mejor marcas?, o como separo Sanofi, Astra, etc!!!
// Precheck de como se cargará la regla?
// ***************************************************************************************

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

		// ITEM TYPE
			$itemtype = 'bonus';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'bonus'; }
			}
			$itemtype = strtolower($itemtype);

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();

		if(WithoutSelectionValue(document.orveefrmrule.itemtype1))
			{ errormessage += "\n- Selecciona el artículo para la regla!."; }

		if(WithoutSelectionValue(document.orveefrmrule.units))
			{ errormessage += "\n- Selecciona las unidades requeridas!."; }
			
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
		 
		   var itemtype0 =  document.getElementById('itemtype0').value;
		   var itemtype1 =  document.getElementById('itemtype1').value;
		 
		   var element =  document.getElementById(htmlelement);
		   
		   if(urlscript.indexOf('?') != -1) {
			   var fragment_url = urlscript+'&ItemType0='+itemtype0+'&ItemType1='+itemtype1;
		   }else{
			   var fragment_url = urlscript+'?ItemType0='+itemtype0+'&ItemType1='+itemtype1;
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
                <input name="s" type="hidden" value="bonus" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
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
                                Nueva Regla Bonificaci&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Art&iacute;culo<br />

                            <table border="0" cellspacing="0" cellpadding="10" style="padding-left:10px;border-left: 1px solid #ADB1BD;border-right: 1px solid #ADB1BD; background-color: #F0F0F0;">
                              <tr>
                                <td>
                                Clasificaci&oacute;n<br />
                                <select name="itemtype0" id="itemtype0" class="selectrequired" onChange="javascript:getItemCount('rules/rules_bonus_itemscount.php', 'DivItemsCount');" >
                                    <option value="">[Ninguno]</option>
                                    <?php
                                        $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																				'BonusItemsOwners','';";
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
                                <span class="textHint"> &middot; Marcas o clasificaci&oacute;n de art&iacute;culos.</span>
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
                                Art&iacute;culo<br />
                                <select name="itemtype1" id="itemtype1" class="selectrequired" onChange="javascript:getItemCount('rules/rules_bonus_itemscount.php', 'DivItemsCount');" style="font-size:9px;" >
                                    <option value="">[Ninguno]</option>
                                    <?php
                                        $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																				'BonusItemsList','';";
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
                                <span class="textHint"> &middot; SKU o Art&iacute;culo.</span>
                                </td>
                              </tr>
                            </table>
                            
                    <span class="textHint"> &middot; Art&iacute;culo para aplicar la regla.</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Unidades<br />
                    <select name="units" id="units" class="selectrequired">
	                    <option value="">[X]</option>
						<?php
							for ($i=1;$i<17;$i++) {
                                    echo "<option value='".$i."'>";
									echo "".$i." unidades</option>";
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Unidades requeridas para obtener una bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Bonificaci&oacute;n<br />
                    <select name="unitsbonus" id="unitsbonus" class="selectrequired">
	                    <option value="0">[Y]</option>
						<?php
							for ($i=1;$i<17;$i++) {
								if ($i == 1) {
                                    echo "<option value='".$i."' selected='selected'>";
									echo "".$i." bonificación</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." bonificaciones</option>";
								}
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Unidades a otorgar de bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Bonificaci&oacute;n Art&iacute;culo<br />
                    <select name="itembonus" id="itembonus" class="selectrequired" style="font-size:9px;" >
                        <option value="">[Mismo Art&iacute;culo]</option>
                        <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'BonusItemsList','';";
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
                    &middot; Art&iacute;culo a otorgar de bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    L&iacute;mite<br />
                    <select name="rangeto" id="rangeto" class="selectrequired">
	                    <option value="9999">[Ilimitado]</option>
						<?php
							for ($i=1;$i<17;$i++) {
                                    echo "<option value='".$i."'>";
									echo "".$i." bonificacion(es) cada 12 meses</option>";
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; L&iacute;mite de unidades de bonificaci&oacute;n.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Cadenas<br />
                    <select name="connection" id="connection" class="selectrequired">
                        <?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
                                                                    'BonusConnectionsList','';";
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
                    &middot; Cadena o Conexi&oacute;n para aplicar al regla.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Destinatarios<br />
                    <input name="rulelist" type="radio" value="0" checked="checked" />&nbsp;Todos los afiliados<br />
					<input name="rulelist" type="radio" value="1" />&nbsp;Solo a estos afiliados o lista:&nbsp; 
                    	<select name="rulelistid" id="rulelistid" class="selectbasic">
                        
							<?php
                                // LISTS
                                $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsList
														'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
                                                        'list', '1', '9999', '','';";
                                $dbtransactions->query($query);
                    
                                // Imprimimos en pantalla cada uno de los parámetros
                                while($my_row=$dbtransactions->get_row()){ 
                            ?>
                                  <option value="<?php echo $my_row['ListId']; ?>">
                                    [<?php echo $my_row['ListId']; ?>]&nbsp;<?php echo urldecode($my_row['ListName']); ?>
                                  </option>
                             <?php
                                } 
                            ?>
                                 
                        </select><br />
                        <span class="textHint">
                        &middot; Lista de afiliados beneficiarios de la regla.
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
                getItemCount('rules/rules_bonus_itemscount.php', 'DivItemsCount'); 
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

