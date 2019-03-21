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
	

	// PARAMETER VALIDATION
		// itemtype
			$itemtype = 'warnings';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'warnings'; }
			}
			$itemtype = strtolower($itemtype);

		// rulemonitor
			$rulemonitor = $_SESSION[$configuration['appkey']]['email'];


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');


?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		var distributionlist = document.orveefrmrule.ruledistributionlist.value;
		var ruleobject = document.orveefrmrule.ruleobject.value;
		var rulelist = document.orveefrmrule.rulelist.value;
		
		//alert(rulelist);
	
		if(WithoutContent(document.orveefrmrule.rulename.value))
			{ errormessage += "\n- Ingrese un nombre para la regla!."; }
			
		if(NoneWithCheck(document.orveefrmrule.ruleobject))
			{ errormessage += "\n- Seleccione un objeto a monitorear!."; }

		if(NoneWithCheck(document.orveefrmrule.ruleoperation))
			{ errormessage += "\n- Seleccione una operación a ejecutar sobre el objeto!."; }

		if (document.orveefrmrule.ruleoperationunits.value == "0" && ruleobject !== "list")
			{ errormessage += "\n- Ingrese las unidades a calcular por la operación!."; }

		if(NoneWithCheck(document.orveefrmrule.ruleschedule))
			{ errormessage += "\n- Seleccione un corte o periodo de monitoreo!."; }

		//if(NoneWithCheck(document.orveefrmrule.ruletype))
		//	{ errormessage += "\n- Un tipo de monitoreo!."; }

		if(NoneWithCheck(document.orveefrmrule.ruleactionstatusid))
			{ errormessage += "\n- Seleccione una acción a realizar!."; }

		if(WithoutContent(document.orveefrmrule.rulecode.value))
			{ errormessage += "\n- Seleccione un código de referencia!."; }

		if (ruleobject == "list" && rulelist == 1)
			{ errormessage += "\n- Para Objeto Lista debes seleccionar una lista de afiliados!."; }
	
		//if (distributionlist.length > 1000)
		//	{ errormessage += "\n- La lista de distribución debe ser de menor tamaño!."; }

			
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
                <input name="s" type="hidden" value="warnings" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="warnings" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="connectionid" type="hidden" value="1" />
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
                                Regla<br />
                                Nueva Regla Alarmas
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
               
                  <tr>
                    <td>
                    Objecto<br />
                    <input name="ruleobject" type="radio" value="transactions" />&nbsp;Transacciones<br />
					<input name="ruleobject" type="radio" value="earned" />&nbsp;Puntos Abonados<br />
					<input name="ruleobject" type="radio" value="redeemed" />&nbsp;Puntos Redimidos<br />
					<input name="ruleobject" type="radio" value="bonus" disabled />&nbsp;<i>Bonificaciones [DISABLED]</i><br />
					<input name="ruleobject" type="radio" value="transfers" disabled />&nbsp;<i>Puntos Transferidos [DISABLED]</i><br />
					<input name="ruleobject" type="radio" value="list" />&nbsp;Lista Afiliados<br />
                        <span class="textHint">
                        &middot; Objeto o hecho a monitorear.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Operaci&oacute;n<br />
                    <input name="ruleoperation" type="radio" value="count" checked="checked" />&nbsp;Contar<br />
					<input name="ruleoperation" type="radio" value="sum" />&nbsp;Sumar [NA @ Transacciones]<br />
					<input name="ruleoperation" type="radio" value="top" disabled />&nbsp;<i>Top [DISABLED]</i><br />
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="ruleoperationunits" id="ruleoperationunits" type="text" class="inputtextrequired" value="0" onkeypress="return CheckCharactersOnly(event,numbers);" /> unidades<br />
                        <span class="textHint">
                        &middot; Operaci&oacute;n a realizar sobre el hecho a monitorear.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Monitoreo<br />
                    <input name="ruleschedule" type="radio" value="hourly" disabled />&nbsp;<i>Por Hora [DISABLED]</i><br />
                    <input name="ruleschedule" type="radio" value="daily" checked="checked" />&nbsp;Diario<br />
					<input name="ruleschedule" type="radio" value="weekly" />&nbsp;Semana<br />
					<input name="ruleschedule" type="radio" value="monthly" />&nbsp;Mes<br />
                        <span class="textHint">
                        &middot; Per&iacute;odo de monitoreo.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      Distribuci&oacute;n<br/>
                    <textarea name="ruledistributionlist" id="ruledistributionlist" cols="80" rows="5" title="Lista Distribucion" maxlength="250" ><?php echo $rulemonitor; ?></textarea><br />
                    
                    <span class="textHint">
                    	&middot; Monitor(es) de la alarma.<br />
                        &middot; Separado por comas [email@dominio.com, email@dominio.com,...].<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Alarma<br />
                    <input name="ruletype" type="radio" value="ordinary" checked="checked" />&nbsp;Ordinaria<br />
					<input name="ruletype" type="radio" value="online" disabled />&nbsp;<i>Online [DISABLED]</i><br />
                        <span class="textHint">
                        &middot; Tipo de Alarma a ejecutar.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Acci&oacute;n<br />
                    <input name="ruleactionstatusid" type="radio" value="1" checked="checked" />&nbsp;No Bloquear<br />
					<input name="ruleactionstatusid" type="radio" value="2" />&nbsp;Bloquear Redenci&oacute;n<br />
					<input name="ruleactionstatusid" type="radio" value="3" />&nbsp;Bloquear Abono & Redenci&oacute;n<br />
                        <span class="textHint">
                        &middot; Acci&oacute;n a realizar sobre los elementos en el monitoreo.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Destinatarios<br />
                    <input name="rulelist" type="radio" value="1" checked="checked" />&nbsp;Todos los afiliados<br />
					<input name="rulelist" type="radio" value="0" />&nbsp;Solo a estos afiliados o lista:&nbsp; 
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
                        &middot; Lista de afiliados a monitorear.<br />
                        &middot; A quien monitoreamos?.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                      Vigencia Inicio<br/>
                      <div><input type="text" name="ruleactivation" id="ruleactivation" value="<?php echo date('d/m/Y'); ?>" class="inputtextrequired" /></div>
			            <span class="textHint">
                    &middot; Fecha programada para inicio de la vigencia.<br />
                    </span></td>
                  </tr>
                  <tr>
                    <td>
                      Vigencia Fin<br/>
                      <div><input type="text" name="ruleexpiration" id="ruleexpiration" value="<?php echo "31/12/2019"; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para fin o termino de la vigencia.<br />
                    </span></td>
                  </tr>

                  <tr>
                    <td>
                      C&oacute;digo<br/>
                    <input name="rulecode" id="rulecode" type="text" class="inputtextrequired" value="CCCCCC" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia de la alarma.<br />
                    </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      Nombre<br/>
                    <input name="rulename" id="rulename" type="text" class="inputtextrequired" size="50" /><br />
                    <span class="textHint">
                    &middot; Nombre para la nueva regla.<br />
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
                        
                    <br /><br />
                    
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

