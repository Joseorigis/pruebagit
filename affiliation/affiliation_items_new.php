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
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 


// --------------------
// INICIO CONTENIDO
// --------------------

	$cardnumberinit = "";
	// Si se envio un número de teléfono...
	if (isset($_GET['q'])) {
		$cardnumberinit = $_GET['q']."*";
	} 

	// AUTHNUMBER for duplicate check
	$affiliationauth = session_id().".".createRandomString(8);

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutContent(document.orveefrmaffiliated.cardnumber.value))
			{ errormessage += "\n- Ingrese una tarjeta!."; }
		else {
			if (document.orveefrmaffiliated.cardnumberstatus.value == 0)
			{ errormessage += "\n- Ingrese un número de tarjeta válido!."; }
		}
		if(WithoutContent(document.orveefrmaffiliated.name.value))
			{ errormessage += "\n- Ingrese un nombre!."; }
		if(WithoutContent(document.orveefrmaffiliated.lastname.value))
			{ errormessage += "\n- Ingrese un apellido!."; }
		if(NoneWithCheck(document.orveefrmaffiliated.gender))
			{ errormessage += "\n- Seleccione un género!."; }
		if(WithoutContent(document.orveefrmaffiliated.day.value))
			{ errormessage += "\n- Ingrese el día de la fecha de nacimiento!."; }
		if(WithoutContent(document.orveefrmaffiliated.month.value))
			{ errormessage += "\n- Ingrese el mes de la fecha de nacimiento!."; }
		if(WithoutContent(document.orveefrmaffiliated.year.value))
			{ errormessage += "\n- Ingrese el año de la fecha de nacimiento!."; }
		if(WithoutContent(document.orveefrmaffiliated.phone.value))
			{ errormessage += "\n- Ingrese un teléfono!."; }
		if(document.orveefrmaffiliated.phone.value != "") {
			if(document.orveefrmaffiliated.phone.value.length < 10)
				{ errormessage += "\n- Ingrese un teléfono completo!."; }
		}
		if(document.orveefrmaffiliated.cellphone.value != "") {
			if(document.orveefrmaffiliated.cellphone.value.length < 10)
				{ errormessage += "\n- Ingrese un teléfono celular completo!."; }
		}
		if(document.orveefrmaffiliated.email.value != "") {
			if (!IsValidEmail(document.orveefrmaffiliated.email.value))
				{ errormessage += "\n- Ingrese un email válido!."; }
		}

		// Fecha Nacimiento
		var fechavalidar  = document.orveefrmaffiliated.month.value+"/"+document.orveefrmaffiliated.day.value+"/"+document.orveefrmaffiliated.year.value;
		// Fecha válida
		if (!IsValidDate(fechavalidar))  
			{ errormessage += "\n- Ingrese una fecha de nacimiento valida!.";	}


		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para afiliar la tarjeta, por favor: ' + errormessage);
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
		
		// USERNAME
		$("#cardnumber").change(function() { 
		
			var cardnumber = $("#cardnumber").val();
			
			if(cardnumber.length > 7 && cardnumber.length < 17) {
		
				// Activamos la imagen de loading...
				$("#cardnumberstatustxt").html('<img src="images/imageloading.gif" align="absmiddle">Verificando...');
		
				$.ajax({  
					type: "POST",  
					url: "affiliation/affiliation_items_newcheck.php",  
					data: "cardnumber="+ cardnumber,  
					success: function(msg){  
			   
						   //$("#cardnumberstatustxt").ajaxComplete(function(event, request, settings){ 	
						   $(document).ajaxComplete(function(event, request, settings){ 	
						
									var cardokfound = msg.indexOf('OK');
									
									//if(msg == 'OK')
									if(cardokfound == 0)
									{ 
										$("#cardnumber").removeClass('inputtextrequired'); // if necessary
										$("#cardnumber").removeClass('inputtextrequirederror'); // if necessary
										$("#cardnumber").addClass("inputtextrequiredok");
										$("#cardnumberstatus").attr('value', '1');
										$("#cardnumberstatustxt").html('&nbsp;<img src="images/bulletcheck.png" align="absmiddle">');
									}  
									else  
									{  
										$("#cardnumber").removeClass('inputtextrequired'); // if necessary
										$("#cardnumber").removeClass('inputtextrequiredok'); // if necessary
										$("#cardnumber").addClass("inputtextrequirederror");
										$("#cardnumberstatus").attr('value', '0');
										$("#cardnumberstatustxt").html(msg);
									}  
				   
							 }); // $("#status").ajaxComplete(function(event, request, settings)
		
					} // success: function(msg)
		   
				 });  // if(usr.length >= 4)
		
			} else {
				
				$("#cardnumberstatustxt").html('<font color="red"><em>La tarjeta debe tener entre 8 y 16 dígitos.</em></font>');
				$("#cardnumber").removeClass('inputtextrequired'); // if necessary
				$("#cardnumber").removeClass('inputtextrequiredok'); // if necessary
				$("#cardnumber").addClass("inputtextrequirederror");
				$("#cardnumberstatus").attr('value', '0');

			} // if(usr.length >= 4)
		
		}); // $("#username").change(function()
	
	}); // $(document).ready(function()
	
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

     function changeAjax (url, comboAnterior, element_id) {
		   var element =  document.getElementById(element_id);
		   var valordepende = document.getElementById(comboAnterior)
		   var x = valordepende.value
		   
		   if(url.indexOf('?') != -1) {
			   var fragment_url = url+'&Id='+x;
		   }else{
			   var fragment_url = url+'?Id='+x;
		   }
		   
		   element.innerHTML = 'Cargando...';
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

                <form action="index.php" method="get" name="orveefrmaffiliated" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="affiliation" />
                <input name="s" type="hidden" value="items" />
                <input name="a" type="hidden" value="add" />
                <input name="cardnumberstatus" id="cardnumberstatus" type="hidden" value="0" />
                <input name="affiliationauth" type="hidden" value="<?php echo $affiliationauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imageuser.gif" alt="Affiliated Status" title="Affiliated Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">Nuevo Afiliado</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
								 &nbsp;
						<?php } else { ?>
								<span style="color:#ff0000;">No tienes privilegios para Afiliar!</span>
						<?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Tarjeta<br />
					<input name="cardnumber" id="cardnumber" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,numbers);" value="<?php echo $cardnumberinit; ?>" />&nbsp;&nbsp;&nbsp;<span id="cardnumberstatustxt"></span><br />
                        <span class="textHint">
                        &middot; N&uacute;mero de tarjeta.
                        </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      Nombre(s)<br />
                        <input name="name" id="name" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" /><br/>
                        <span class="textHint">
                      &middot; Nombre del paciente.</span>
                      </td>
                  </tr>
                  <tr>
                    <td>
                      Apellidos<br/>
                        <input name="lastname" id="lastname" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" size="50" /><br />
                        <span class="textHint">
                    &middot; Apellidos del paciente.</span></td>
                  </tr>
                  <tr>
                    <td>
                    G&eacute;nero<br />
                    <div class="fieldrequired">
                    <input name="gender" type="radio" value="1" />&nbsp;Masculino<br />
					<input name="gender" type="radio" value="2" />&nbsp;Femenino<br />
					</div>
                        <span class="textHint">
                      &middot; G&eacute;nero o sexo del paciente.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Fecha Nacimiento<br/>
         			D&iacute;a:&nbsp;
         			<input name="day" type="text" class="inputtextrequired" size="2" maxlength="2" value="DD" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" onKeyUp="GoToNextField(this,2,'month');" />&nbsp;&nbsp;
                    Mes:&nbsp;
			        <input name="month" type="text" class="inputtextrequired" size="2" maxlength="2" value="MM" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" onKeyUp="GoToNextField(this,2,'year');" />&nbsp;&nbsp;
                    A&ntilde;o:&nbsp;
			       <input name="year" type="text" class="inputtextrequired" size="4" maxlength="4" value="AAAA" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" />
                    <br />
                        <span class="textHint">
                      &middot; Fecha de nacimiento del paciente.</span>
                     </td>
                  </tr>
                  <tr>
                    <td>
                    Email<br />
                    <input name="email" id="email" type="text" class="inputtext" size="50" /><br />
                    <span class="textHint">&middot; Email del paciente.</span>
					</td>
                  </tr>
                  <tr>
                    <td>
                    Tel&eacute;fono<br />
                    <input name="phone" id="phone" type="text" class="inputtextrequired" size="20" onkeypress="return CheckCharactersOnly(event,numbers);" /><br />
                    <span class="textHint"> &middot; N&uacute;mero de tel&eacute;fono.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Celular<br />
                    <input name="cellphone" id="cellphone" type="text" class="inputtext" size="20" onkeypress="return CheckCharactersOnly(event,numbers);" /><br />
                    <span class="textHint"> &middot; N&uacute;mero de tel&eacute;fono celular.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Permiso Contacto<br />
                    <input name="permission"  id="permission" type="checkbox" value="" checked="checked" /> Aceptaci&oacute;n de ser contactado<br />
                    <span class="textHint"> &middot; Permiso para contactar al afiliado.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    M&eacute;dico<br />
                    <input name="doctorname" id="doctorname" type="text" class="inputtext" size="50" value="" /><br />
                    <span class="textHint"> 
                    	&middot; Informaci&oacute;n adicional del afiliado.<br />
                    	&middot; e.g. Nombre del m&eacute;dico.<br />
                    </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                        <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
								 $_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
								<div id="botonsubmit">
								<input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
								</div>
						<?php } else { ?>
								<span style="color:#ff0000;">No tienes privilegios para Afiliar!</span>
						<?php } ?>
                    </td>
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

