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
		// ERROR MESSAGE
		$errormessage = "";


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}
	

	// PARAMETER VALIDATION
		// itemtype
			$itemtype = 'connections';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'connections'; }
			}
			$itemtype = strtolower($itemtype);


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

<SCRIPT type="text/javascript">
<!--
	function setConnOrbisApp() {
	
		var isorbisapp = document.getElementById("orbisapp").checked;
		var licenses = document.getElementById("connectionlicenses").value;
		
		if (isorbisapp) {
			document.getElementById("connectionwebservice").value = 'https://orbiswsapp02.orbisfarma.com.mx/';
			if (licenses == 1)
				{ document.getElementById("connectionlicenses").value = '3'; }
		} else {
			document.getElementById("connectionwebservice").value = 'https://orbisws01.orbisfarma.com.mx/';
			document.getElementById("connectionlicenses").value = '1';
		}
		
		
	}
	
	function CheckRequiredFields() {
		var errormessage = new String();
		
		var isorbisapp = document.getElementById("orbisapp").checked;
		var licenses = document.getElementById("connectionlicenses").value;
		
		if(WithoutContent(document.orveefrmhelpdesk.connectionname.value))
			{ errormessage += "\n- Ingrese un nombre para la conexión!."; }
			
		if(NoneWithCheck(document.orveefrmhelpdesk.connectiontype))
			{ errormessage += "\n- Seleccione un tipo de conexión!."; }

		if(NoneWithCheck(document.orveefrmhelpdesk.connectionapp))
			{ errormessage += "\n- Seleccione la aplicación para la conexión!."; }

		if(WithoutContent(document.orveefrmhelpdesk.connectionwebservice.value))
			{ errormessage += "\n- Ingrese un web service para la conexión!."; }

		if(WithoutContent(document.orveefrmhelpdesk.connectionactivation.value))
			{ errormessage += "\n- Ingrese un fecha de inicio para la conexión!."; }
			
		if(WithoutContent(document.orveefrmhelpdesk.connectionexpiration.value))
			{ errormessage += "\n- Ingrese un fecha de fin para la conexión!."; }
			
			if  (isorbisapp == false && licenses > 1)
				{ errormessage += "\n- La aplicación para la conexión seleccionada solo puede usar una licencia!."; }


		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar la conexión, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}
		//document.orveefrmuser.submit();
		return true;
	} // end of function CheckRequiredFields()



	/*
	Credits: Bit Repository
	Source: http://www.bitrepository.com/web-programming/ajax/username-checker.html 
	*/

	pic1 = new Image(16, 16); 
	pic1.src = "images/imageloading.gif";
	
	$(document).ready(function(){
		
		// connectionname
		$("#connectionname").change(function() { 
		
			var connectionname = $("#connectionname").val();
			
			if(connectionname.length > 0 && connectionname.length < 255) {
		
				// Activamos la imagen de loading...
				$("#connectionnamestatustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
		
				$.ajax({  
					type: "POST",  
					url: "helpdesk/helpdesk_connections_newcheck.php",  
					data: "q="+ connectionname,  
					success: function(msg){  
			   
						   //$("#cardnumberstatustxt").ajaxComplete(function(event, request, settings){ 	
						   $(document).ajaxComplete(function(event, request, settings){ 	
						
									var cardokfound = msg.indexOf('OK');
									
									//if(msg == 'OK')
									if(cardokfound == 0)
									{ 
										$("#connectionname").removeClass('inputtextrequired'); // if necessary
										$("#connectionname").removeClass('inputtextrequirederror'); // if necessary
										$("#connectionname").addClass("inputtextrequiredok");
										$("#connectionnamestatustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
									}  
									else  
									{  
										$("#connectionname").removeClass('inputtextrequired'); // if necessary
										$("#connectionname").removeClass('inputtextrequiredok'); // if necessary
										$("#connectionname").addClass("inputtextrequirederror");
										$("#connectionnamestatustxt").html(msg);
									}  
				   
							 }); // $("#status").ajaxComplete(function(event, request, settings)
		
					} // success: function(msg)
		   
				 });  // if(usr.length >= 4)
		
			} else {
				
				$("#connectionnamestatustxt").html('<font color="red"><em>El nombre de la conexión no puede estar vacio.</em></font>');
				$("#connectionname").removeClass('inputtextrequired'); // if necessary
				$("#connectionname").removeClass('inputtextrequiredok'); // if necessary
				$("#connectionname").addClass("inputtextrequirederror");

			} // if(usr.length >= 4)
		
		}); // $("#connectionname").change(function()
	
	}); // $(document).ready(function()

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

                <form action="index.php" method="get" name="orveefrmhelpdesk" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="helpdesk" />
                <input name="s" type="hidden" value="connections" />
                <input name="a" type="hidden" value="add" />
                <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagesettings.png" alt="Help Desk" title="Help Desk" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Help Desk<br />
                                Nueva Conexi&oacute;n
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Conexi&oacute;n<br/>
                    <input name="connectionname" id="connectionname" type="text" class="inputtextrequired" size="50" />&nbsp;&nbsp;&nbsp;<span id="connectionnamestatustxt"></span><br />
                    <span class="textHint">
                    &middot; Nombre de la conexi&oacute;n.<br />
                    </span></td>
                  </tr>

                  <tr>
                    <td>
                      C&oacute;digo<br/>
                    <input name="connectioncode" id="connectioncode" type="text" class="inputtext" /><br />
                    <span class="textHint">
                    &middot; C&oacute;digo de referencia de la conexi&oacute;n.<br />
                    </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Tipo<br />
                    <div class="fieldrequired">
                    <input name="connectiontype" type="radio" value="cadena" />&nbsp;Cadena<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Cadena con presencia nacional.
                         </span></div>
					<input name="connectiontype" type="radio" value="autoservicio" />&nbsp;Auto Servicio<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Tiendas de autoservicio.
                         </span></div>
                    <input name="connectiontype" type="radio" value="retail" />&nbsp;Retail<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Retail o tiendas de consumo, que no son farmacias.
                         </span></div>
					<input name="connectiontype" type="radio" value="aliado" />&nbsp;Aliado<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Conexi&oacute;n o integraci&oacute;n con terceros, aliados o puntos de venta.
                         </span></div>
					<input name="connectiontype" type="radio" value="farmacia" />&nbsp;Farmacia<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Farmacias con presencia en una regi&oacute;n y con varias sucursales.
                         </span></div>
					<input name="connectiontype" type="radio" value="farmaciaindependiente" />&nbsp;Farmacia Independiente<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Farmacias independientes con una o algunas sucursales.
                         </span></div>
					<input name="connectiontype" type="radio" value="orbis" />&nbsp;Orbis<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Uso interno.
                         </span></div>
					<input name="connectiontype" type="radio" value="farmaciaindependientemarzam" />&nbsp;Marzam<br />
                         <div style="padding-left:50px"><span style="font-size:8px; font-style:italic;">
                         &middot; Farmacias independientes del Grupo Marzam [EXCEPCION].
                         </span></div>
                    </div>
                        <span class="textHint">
                        &middot; Tipo de conexi&oacute;n.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    APP<br />
                    <div class="fieldrequired">
                    <input name="connectionapp" id="orbisapp" type="radio" value="orbisapp" onClick="setConnOrbisApp(this);" />&nbsp;OrbisApp<br />
					<input name="connectionapp" id="orbiswebservice" type="radio" value="orbiswebservice" onClick="setConnOrbisApp(this);" />&nbsp;Web Service<br />
					<input name="connectionapp" id="orbisoffline" type="radio" value="orbisoffline" onClick="setConnOrbisApp(this);" />&nbsp;Offline<br />
                    </div>
                        <span class="textHint">
                        &middot; Aplicaci&oacute;n o forma de conexi&oacute;n.<br />
                        </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Web Service<br />
                    <input name="connectionwebservice" id="connectionwebservice" type="text" class="inputtextrequired" size="80" value="https://orbisws01.orbisfarma.com.mx/" />
                        <span class="textHint">
                        &middot; Servicio web o aplicaci&oacute;n de la conexi&oacute;n.<br />
                        </span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Licencias<br />
                    <select name="connectionlicenses" id="connectionlicenses" class="selectrequired">
	                    <option value="">[Seleccione Licencias]</option>
						<?php
							for ($i=1;$i<10;$i++) {
								if ($i == 1) {
                                    echo "<option value='".$i."' selected>";
									echo "".$i." licencia</option>";
								} else {
                                    echo "<option value='".$i."'>";
									echo "".$i." licencias</option>";
								}
							}
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Cantidad de licencias disponibles para la conexi&oacute;n [ORBISAPP].<br />
                    </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    Sucursal &Uacute;nica<br />
                    <input name="singlestore"  id="singlestore" type="checkbox" value="" /> Solo es una sucursal o tienda.<br />
                    <span class="textHint"> &middot; Sucursales de la cadena.</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      Vigencia Inicio<br/>
                      <div><input type="text" name="connectionactivation" id="connectionactivation" value="<?php echo date('d/m/Y'); ?>" class="inputtextrequired" /></div>
			            <span class="textHint">
                    &middot; Fecha programada para inicio de la conexi&oacute;n.<br />
                    </span></td>
                  </tr>
                  <tr>
                    <td>
                      Vigencia Fin<br/>
                      <div><input type="text" name="connectionexpiration" id="connectionexpiration" value="<?php echo "31/12/2019"; ?>" class="inputtextrequired" readonly /></div>
			            <span class="textHint">
                    &middot; Fecha programada para fin o termino de la conexi&oacute;n.<br />
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
                    $('#connectionactivation').jdPicker({
                        date_format:"dd/mm/YYYY", 
                        //select_week:1, 
                        show_week:1, 
                        week_label:"sem", 
                        //selectable_days:[1, 2, 3, 4, 5, 6], 
                        start_of_week:0, 
                        date_min:today
                    });
                    $('#connectionexpiration').jdPicker({
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

