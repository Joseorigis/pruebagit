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

// -----------------------------

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


			// ACCESS IP
			$securityaccessip = "True";
			$query  = "EXEC dbo.usp_app_ParametersList 'Security','IPFilter';";
			$dbsecurity->query($query);
			$my_row=$dbsecurity->get_row();
			$securityaccessip = $my_row['ParameterValue'];

			// USERNAME FORMATION
			$securityusernameformation = "True";
			$query  = "EXEC dbo.usp_app_ParametersList 'Security','UsernameLength&Formation';";
			$dbsecurity->query($query);
			$my_row=$dbsecurity->get_row();
			$securityusernameformation = $my_row['ParameterValue'];

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		// Put field checks below this point.
		
		if(WithoutContent(document.orveefrmuser.username.value))
			{ errormessage += "\n- Ingresa un usuario!."; }
		if(WithoutContent(document.orveefrmuser.email.value))
			{ errormessage += "\n- Ingresa un email!."; }
		if(WithoutContent(document.orveefrmuser.name.value))
			{ errormessage += "\n- Ingresa un nombre!."; }
		if(WithoutContent(document.orveefrmuser.lastname.value))
			{ errormessage += "\n- Ingresa un apellido!."; }
		if(NoneWithCheck(document.orveefrmuser.userprofileid))
			{ errormessage += "\n- Selecciona un perfil de seguridad!."; }
		
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
		
		// USERNAME
		$("#username").change(function() { 
		
			var usr = $("#username").val();
			
			<?php if ($securityusernameformation == 'False') { ?>
				var passed = validatePassword(usr, {
					length:   [6, 20],
					lower:    0,
					upper:    0,
					numeric:  0,
					special:  0,
					badWords: ["password", "contrasena"],
					badSequenceLength: 4
				});
			<?php } else { ?>
				var passed = validatePassword(usr, {
					length:   [8, 20],
					lower:    1,
					upper:    0,
					numeric:  1,
					special:  0,
					badWords: ["password", "contrasena"],
					badSequenceLength: 4
				});
			<?php }  ?>

			//if(usr.length >= 4) {
			if(passed) {
		
				// Activamos la imagen de loading...
				$("#usernamestatustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
		
				$.ajax({  
					type: "POST",  
					url: "security/security_users_newcheck.php",  
					data: "username="+ usr,  
					success: function(msg){  
			   
						   $(document).ajaxComplete(function(event, request, settings){ 	
						
									if(msg == 'OK')
									{ 
										$("#username").removeClass('inputtextrequired'); // if necessary
										$("#username").removeClass('inputtextrequirederror'); // if necessary
										$("#username").addClass("inputtextrequiredok");
										//$("#submitbutton").attr('disabled', 'disabled');
										//$("#submitbutton").removeAttr('disabled');
										$("#usernamestatus").attr('value', '1');
										$("#usernamestatustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
										if ($("#usernamestatus").val() == '1' && $("#emailstatus").val() == '1') {
											$("#submitbutton").removeAttr('disabled');
										} else {
										   $("#submitbutton").attr('disabled', 'disabled');
										}
									}  
									else  
									{  
										$("#username").removeClass('inputtextrequired'); // if necessary
										$("#username").removeClass('inputtextrequiredok'); // if necessary
										$("#username").addClass("inputtextrequirederror");
										//$("#submitbutton").attr('disabled', 'disabled');
										$("#usernamestatus").attr('value', '0');
										$("#usernamestatustxt").html(msg);
										if ($("#usernamestatus").val() == '1' && $("#emailstatus").val() == '1') {
											$("#submitbutton").removeAttr('disabled');
										} else {
										   $("#submitbutton").attr('disabled', 'disabled');
										}
									}  
				   
							 }); // $("#status").ajaxComplete(function(event, request, settings)
		
					} // success: function(msg)
		   
				 });  // if(usr.length >= 4)
		
			} else {
				
				$("#usernamestatustxt").html('<font color="red"><em>El nombre de usuario debe contener al menos una letra y un número, y tener una longitud mínima de 8 caracteres.</em></font>');
				$("#username").removeClass('inputtextrequired'); // if necessary
				$("#username").removeClass('inputtextrequiredok'); // if necessary
				$("#username").addClass("inputtextrequirederror");
				
			} // if(usr.length >= 4)
		
		}); // $("#username").change(function()
	
	
		// EMAIL
		$("#email").change(function() { 
		
			var email = $("#email").val();
			//if(usr.length >= 4) {
			if (IsValidEmail(email)) {
		
				// Activamos la imagen de loading...
				$("#emailstatustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
		
				$.ajax({  
					type: "POST",  
					url: "security/security_users_newcheck.php",  
					data: "email="+ email,  
					success: function(msg){  
			   
						   $(document).ajaxComplete(function(event, request, settings){ 	
						
									if(msg == 'OK')
									{ 
										$("#email").removeClass('inputtextrequired'); // if necessary
										$("#email").removeClass('inputtextrequirederror'); // if necessary
										$("#email").addClass("inputtextrequiredok");
										//$("#submitbutton").attr('disabled', 'disabled');
										//$("#submitbutton").removeAttr('disabled');
										$("#emailstatus").attr('value', '1');
										$("#emailstatustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
										if ($("#usernamestatus").val() == '1' && $("#emailstatus").val() == '1') {
											$("#submitbutton").removeAttr('disabled');
										} else {
										   $("#submitbutton").attr('disabled', 'disabled');
										}
									}  
									else  
									{  
										$("#email").removeClass('inputtextrequired'); // if necessary
										$("#email").removeClass('inputtextrequiredok'); // if necessary
										$("#email").addClass("inputtextrequirederror");
										//$("#submitbutton").attr('disabled', 'disabled');
										$("#emailstatus").attr('value', '0');
										$("#emailstatustxt").html(msg);
										if ($("#usernamestatus").val() == '1' && $("#emailstatus").val() == '1') {
											$("#submitbutton").removeAttr('disabled');
										} else {
										   $("#submitbutton").attr('disabled', 'disabled');
										}
									}  
				   
							 }); // $("#status").ajaxComplete(function(event, request, settings)
		
					} // success: function(msg)
		   
				 });  // if(usr.length >= 4)
		
			} else {
				
				$("#emailstatustxt").html('<font color="red"><em>El email ingresado no es válido!.</em></font>');
				$("#email").removeClass('inputtextrequired'); // if necessary
				$("#email").removeClass('inputtextrequiredok'); // if necessary
				$("#email").addClass("inputtextrequirederror");
				
			} // if(usr.length >= 4)
		
		}); // $("#email").change(function()
		
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

                <form action="index.php" method="get" name="orveefrmuser" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="security" />
                <input name="s" type="hidden" value="users" />
                <input name="a" type="hidden" value="add" />
                <input name="actionauth" id="actionauth" type="hidden" value="0<?php echo $actionauth; ?>" />
                <input name="usernamestatus" id="usernamestatus" type="hidden" value="0" />
                <input name="emailstatus" id="emailstatus" type="hidden" value="0" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imageuser.gif" alt="User Status" title="User Status" class="imagensecurityuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">Nuevo Usuario</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Usuario<br />
					<input name="username" id="username" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,loginchars);" />&nbsp;&nbsp;&nbsp;<span id="usernamestatustxt"></span><br />
                        <span class="textHint">
                        &middot; Nombre de usuario o inicio de sesi&oacute;n para el acceso a la aplicación.<br />
                        &middot; Incluir letras y n&uacute;meros, al menos 8 caracteres.
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Nombre<br />
                    <input name="name" id="name" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" /><br />
                        <span class="textHint">
                        &middot; Nombre(s) del usuario.<br />
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Apellidos<br />
                    <input name="lastname" id="lastname" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" /><br />
                         <span class="textHint">
                        &middot; Apellido(s) del usuario.<br />
                        </span>
                   </td>
                  </tr>
                  <tr>
                    <td>
                    Email<br />
                    <input name="email" id="email" type="text" class="inputtextrequired" size="50" />&nbsp;&nbsp;<span id="emailstatustxt"></span><br />
                         <span class="textHint">
                        &middot; Email del usuario.<br />
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Perfil Seguridad&nbsp;<span style="color:#F00;font-weight: bold;font-size: 12px;">|</span><br />
					<?php
                        $query  = " EXEC dbo.usp_app_UtilityCategoryElements 'UserProfile','';";
                        $dbsecurity->query($query);
                        while($my_row=$dbsecurity->get_row()){ 
							if ($my_row['ItemIsSelected'] == 1) {
                                echo "<input name='userprofileid' type='radio' value='".$my_row['ItemId']."' checked='checked' />&nbsp;".$my_row['Item']."<br />";
							} else {
                                echo "<input name='userprofileid' type='radio' value='".$my_row['ItemId']."' />&nbsp;".$my_row['Item']."<br />";
							}
                        }
                    ?>
                         <span class="textHint">
                        &middot; Perfil y privilegios de acceso del usuario.<br />
                        </span>
                    </td>
                  </tr>
                  
				<?php
				if ($securityaccessip == "True") {
                ?>
                      <tr>
                        <td>
                        IP Acceso<br />
                        <input name="ipaccess" id="ipaccess" type="text" class="inputtext" size="15" value="*.*.*.*" readonly /><br />
                             <span class="textHint">
                            &middot; Restricci&oacute;n en direcci&oacute;n IP para acceso.<br />
                            </span>
                        </td>
                      </tr>
                <?php } else { ?>  
                		<input name="ipaccess" id="ipaccess" type="hidden" value="*.*.*.*" />
                        <!--<input name="ipaccess" id="ipaccess" type="text" class="inputtext" size="15" onchange="verifyIP(ipaccess.value);" /><br />-->

                <?php } ?>  
                  <tr>
                    <td>
                    Vigencia<br />
                    <input name="expiredate" id="expiredate" type="text" class="inputtext" value="20990101" readonly /><br />
                         <span class="textHint">
                        &middot; Fecha de vigencia o l&iacute;mite de fecha de acceso.<br />
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Cambio Password<br />
                    <input name="changepassword" type="checkbox" value="changepassword" checked="checked" /><br />
                         <span class="textHint">
                        &middot; Solicitar un cambio de contrase&ntilde;a la pr&oacute;xima vez que acceda.<br />
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

