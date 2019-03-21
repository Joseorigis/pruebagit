<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* security_x.php
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
		if ($requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}
		

	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		// Put field checks below this point.
		
		if(WithoutContent(document.orveefrmuser.passwordcurrent.value))
			{ errormessage += "\n- Ingresa tu contraseña actual!."; }
		if(WithoutContent(document.orveefrmuser.passwordnew1.value))
			{ errormessage += "\n- Ingresa tu contraseña nueva!."; }
		if(WithoutContent(document.orveefrmuser.passwordnew2.value))
			{ errormessage += "\n- Ingresa la confirmación de tu contraseña nueva!.<br />"; }
		if(document.orveefrmuser.password1status.value == '0')
			{ errormessage += "\n- La contraseña nueva es incorrecta!.<br />"; }
		if(document.orveefrmuser.password2status.value == '0')
			{ errormessage += "\n- La confirmación de contraseña nueva es incorrecta!.<br />"; }
		if(document.orveefrmuser.passwordcurrent.value == document.orveefrmuser.passwordnew1.value)
			{ errormessage += "\n- La contraseña nueva no puede ser igual a la actual!.<br />"; }
		
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
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
		
		// PASSWORD1
		$("#passwordnew1").change(function() { 
		
				var password = $("#passwordnew1").val();
				
				var passed = validatePassword(password, {
					length:   [8, Infinity],
					lower:    1,
					upper:    0,
					numeric:  1,
					special:  1,
					badWords: ["password", "steven", "levithan"],
					badSequenceLength: 4
				});
				
				// Para validar si el password actual y nuevo son diferentes
				var different = true;
				if (password == $("#passwordcurrent").val()) {
					different = false;
					passed = false;
				}

				if (password == $("#username").val()) {
					different = false;
					passed = false;
				}

				//if(usr.length >= 4) {
				if(passed) {
			
					// Activamos la imagen de loading...
					//$("#passwordnew1statustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
			
					$("#passwordnew1").removeClass('inputtextrequired'); // if necessary
					$("#passwordnew1").removeClass('inputtextrequirederror'); // if necessary
					$("#passwordnew1").addClass("inputtextrequiredok");
					//$("#submitbutton").attr('disabled', 'disabled');
					//$("#submitbutton").removeAttr('disabled');
					$("#password1status").attr('value', '1');
					$("#passwordnew2").attr('value', '');
					$("#password2status").attr('value', '0');
					$("#passwordnew1statustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
					$("#passwordnew2statustxt").html('&nbsp;');
					$("#passwordnew2").removeClass('inputtextrequiredok'); // if necessary
					$("#passwordnew2").removeClass('inputtextrequirederror'); // if necessary
					$("#passwordnew2").addClass("inputtextrequired");
					if ($("#password1status").val() == '1' && $("#password2status").val() == '1') {
						$("#submitbutton").removeAttr('disabled');
					} else {
					   $("#submitbutton").attr('disabled', 'disabled');
					}
					   
				} else {
					
					$("#passwordnew1").removeClass('inputtextrequired'); // if necessary
					$("#passwordnew1").removeClass('inputtextrequiredok'); // if necessary
					$("#passwordnew1").addClass("inputtextrequirederror");
					$("#password1status").attr('value', '0');
					$("#passwordnew2").attr('value', '');
					$("#passwordnew2statustxt").html('&nbsp;');
					$("#passwordnew2").removeClass('inputtextrequiredok'); // if necessary
					$("#passwordnew2").removeClass('inputtextrequirederror'); // if necessary
					$("#passwordnew2").addClass("inputtextrequired");
					if ($("#password1status").val() == '1' && $("#password2status").val() == '1') {
						$("#submitbutton").removeAttr('disabled');
					} else {
					   $("#submitbutton").attr('disabled', 'disabled');
					}
					if (different) {
						$("#passwordnew1statustxt").html('<font color="red"><em>La contrase&ntilde;a debe contener al menos una letra, un número, un caracter especial, y tener una longitud mínima de 8 caracteres.</em></font>');
					} else {
						$("#passwordnew1statustxt").html('<font color="red"><em>La contrase&ntilde;a nueva no puede ser igual a la actual.</em></font>');
					}
					
				} // if(usr.length >= 4)
		
		}); // $("#password").change(function()

		// PASSWORD2
		$("#passwordnew2").change(function() { 
		
				var password = $("#passwordnew2").val();
				
				var passed = validatePassword(password, {
					length:   [8, Infinity],
					lower:    1,
					upper:    0,
					numeric:  1,
					special:  0,
					badWords: ["password", "steven", "levithan"],
					badSequenceLength: 4
				});
				
				//if(passed) {
				if ($("#passwordnew1").val() == password) {
			
					// Activamos la imagen de loading...
					//$("#passwordnew1statustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
			
					$("#passwordnew2").removeClass('inputtextrequired'); // if necessary
					$("#passwordnew2").removeClass('inputtextrequirederror'); // if necessary
					$("#passwordnew2").addClass("inputtextrequiredok");
					//$("#submitbutton").attr('disabled', 'disabled');
					//$("#submitbutton").removeAttr('disabled');
					$("#password2status").attr('value', '1');
					$("#passwordnew2statustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
					if ($("#password1status").val() == '1' && $("#password2status").val() == '1') {
						$("#submitbutton").removeAttr('disabled');
					} else {
					   $("#submitbutton").attr('disabled', 'disabled');
					}
					   
				} else {
					
					$("#passwordnew2statustxt").html('<font color="red"><em>La confirmaci&oacute;n de contrase&ntilde;a debe coincidir con la contrase&ntilde;a nueva.</em></font>');
					$("#passwordnew2").removeClass('inputtextrequired'); // if necessary
					$("#passwordnew2").removeClass('inputtextrequiredok'); // if necessary
					$("#passwordnew2").addClass("inputtextrequirederror");
					$("#password2status").attr('value', '0');
					if ($("#password1status").val() == '1' && $("#password2status").val() == '1') {
						$("#submitbutton").removeAttr('disabled');
					} else {
					   $("#submitbutton").attr('disabled', 'disabled');
					}
					
				} // if(usr.length >= 4)
		
		}); // $("#password").change(function()
		
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

                    <?php
                    
                        // Obtengo el índice del paginado
						$query  = "EXEC dbo.usp_app_SecurityUserManage 
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'view', 
											'".$_SESSION[$configuration['appkey']]['userid']."';";
                        $dbsecurity->query($query);
                        $my_row=$dbsecurity->get_row();
                        $userid = $_SESSION[$configuration['appkey']]['userid'];
                        
                        // Imagen en el output
						$icono = "images/imageuser.gif";
						if ($my_row['UserStatusId'] == 1)  { $icono = "images/imageuseractive.gif"; }
						if ($my_row['UserStatusId'] == 3)  { $icono = "images/imageuserwarning.gif"; }
						if ($my_row['UserStatusId'] == 6)  { $icono = "images/imageuserinactive.gif"; }
						
						if ($my_row['UserProfileId'] == 1) { $icono = "images/imageuseradmin.gif"; }
						if ($my_row['UserProfileId'] == 2) { $icono = "images/imageuseradmin.gif"; }	
                    
                    ?>

                <form action="index.php" method="get" name="orveefrmuser" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="security" />
                <input name="s" type="hidden" value="users" />
                <input name="a" type="hidden" value="passwordupdate" />
                <input name="n" type="hidden" value="<?php echo $_SESSION[$configuration['appkey']]['userid']; ?>" />
                <input name="actionauth" id="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="username" id="username" type="hidden" value="<?php echo $_SESSION[$configuration['appkey']]['username']; ?>" />
                <input name="password1status" id="password1status" type="hidden" value="0" />
                <input name="password2status" id="password2status" type="hidden" value="0" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="<?php echo $icono; ?>" alt="User Status" title="User Status" class="imagensecurityuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium"><?php echo $my_row['UserStatus']; ?></span><br />
                                <?php echo $my_row['UserStatusDescription']; ?><br />
                                <?php echo $my_row['UserProfile']; ?><br />
                                </td>
                              </tr>
                            </table>
                            <br />
                            <em><span class="textMedium"><?php echo $_SESSION[$configuration['appkey']]['username']; ?></span></em><br />
                            <span class="textMedium"><?php echo $_SESSION[$configuration['appkey']]['name']; ?></span>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Contrase&ntilde;a Actual<br />
					<input name="passwordcurrent" id="passwordcurrent" type="password" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,loginchars);" /><br />
                        <span class="textHint">
                        &middot; Contrase&ntilde;a actual.
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Contrase&ntilde;a Nueva<br />
                    <input name="passwordnew1" id="passwordnew1" type="password" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,loginchars);" />&nbsp;&nbsp;&nbsp;<span id="passwordnew1statustxt"></span><br />
                        <span class="textHint">
                        &middot; Contrase&ntilde;a nueva.<br />
                        &middot; Incluir letras y n&uacute;meros, al menos 8 caracteres.
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Contrase&ntilde;a Nueva Confirmaci&oacute;n<br />
                    <input name="passwordnew2" id="passwordnew2" type="password" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,loginchars);" />&nbsp;&nbsp;&nbsp;<span id="passwordnew2statustxt"></span><br />
                         <span class="textHint">
                        &middot; Confirmar contrase&ntilde;a nueva.<br />
                        </span>
                   </td>
                  </tr>
                  <tr>
                    <td><input name="submitbutton" id="submitbutton" type="submit" value="Guardar" disabled="disabled" /></td>
                  </tr>
                </table>
				</form>
                
                
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

