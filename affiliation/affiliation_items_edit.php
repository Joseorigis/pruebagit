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

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

	// ERROR ID 
		$actionerrorid = 0;
		$items = 0;
		
	// AFFILIATIONID
			// Obtenemos el ID de la afiliación
			$affiliationid = 0;
			if (isset($_GET['n'])) {
				$affiliationid = trim($_GET['n']);
				if ($affiliationid == "") { $affiliationid = 0; }
				if (!is_numeric($affiliationid)) { $affiliationid = "0"; }
			}

		// AUTHNUMBER for duplicate check
		$actionauth = "";
		$actionauth = session_id().".".$affiliationid.".".createRandomString(8);


	// AFFILIATIONSEARCH
			// Obtengo el índice del paginado
			$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
								'".$affiliationid."', '0';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows();
			$my_row=$dbconnection->get_row();

			$affiliationcard 	 = $my_row['CardNumber'];
			$affiliationstatusid = $my_row['CardStatusId'];
			$affiliationname	 = $my_row['CardName'];

			// Imagen en el output
			$affiliatedimage = "images/imageuser.gif";
			$affiliatedicon = "images/bulletapply.png";
			if ($affiliationstatusid == 1) { $affiliatedimage = "images/imageuseractive.gif"; }
			if ($affiliationstatusid == 3) { $affiliatedimage = "images/imageuserwarning.gif"; }
			if ($affiliationstatusid == 4) { $affiliatedimage = "images/imageuserinactive.gif"; }
			if ($affiliationstatusid == 6) { $affiliatedimage = "images/imageuserdeleted.gif"; }
			if ($affiliationstatusid  > 1) { $affiliatedicon = "images/bulletblock.png"; }
	

	// AFFILIATIONVIEW
			$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemManage
								'updateview', 
								'crm',
								'".$_SESSION[$configuration['appkey']]['userid']."',
								'".$script."',
								'".$affiliationid."',
								'".$affiliationcard."';";
			$dbconnection->query($query);
			$my_row=$dbconnection->get_row();
			
?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
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
			alert('Para actualizar la tarjeta, por favor: ' + errormessage);
			
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

                <form action="index.php" method="get" name="orveefrmaffiliated" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="affiliation" />
                <input name="s" type="hidden" value="items" />
                <input name="a" type="hidden" value="update" />
                <input name="n" id="n" type="hidden" value="<?php echo $affiliationid; ?>" />
                <input name="cardnumber" id="cardnumber" type="hidden" value="<?php echo $affiliationcard; ?>" />
                <input name="affiliationauth" id="affiliationauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Status" title="Status" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                <?php echo $affiliationcard; ?><br />
                                <span class="textSmall"><?php echo $affiliationname; ?><br /></span>
                                Actualizar Afiliado
                                </span><br />
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
								<span style="color:#ff0000;">No tienes privilegios para Editar!</span>
						<?php } ?>
                    </td>
                  </tr>                  
                  <tr>
                    <td>
                      Nombre(s)<br />
                      <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 3 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 5 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 6 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 8) { ?>
                        	<input name="name" id="name" type="text" class="inputtextreadonly" onkeypress="return CheckCharactersOnly(event,letters);" value="<?php echo trim($my_row['CardName']); ?>" readonly /><br/>
                      <?php } else { ?>      
                        	<input name="name" id="name" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" value="<?php echo trim($my_row['CardName']); ?>" /><br/>
                      <?php } ?>      
                        <span class="textHint">
                      &middot; Nombre del afiliado.</span>
                      </td>
                  </tr>
                  <tr>
                    <td>
                      Apellidos<br/>
                      <?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 3 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 5 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 6 ||
								$_SESSION[$configuration['appkey']]['userprofileid'] == 8) { ?>
                        	<input name="lastname" id="lastname" type="text" class="inputtextreadonly" onkeypress="return CheckCharactersOnly(event,letters);" size="50" value="<?php echo trim($my_row['CardLastName']); ?>" readonly /><br />
                      <?php } else { ?>      
                        	<input name="lastname" id="lastname" type="text" class="inputtextrequired" onkeypress="return CheckCharactersOnly(event,letters);" size="50" value="<?php echo trim($my_row['CardLastName']); ?>" /><br />
                      <?php } ?>      
                        <span class="textHint">
                    &middot; Apellidos del afiliado.</span></td>
                  </tr>
                  <tr>
                    <td>
                    G&eacute;nero<br />
                    <div class="fieldrequired">
                    <input name="gender" type="radio" value="1" <?php if($my_row['CardGenderId'] == 1) { echo 'checked="checked"'; } ?> />&nbsp;Masculino<br />
					<input name="gender" type="radio" value="2" <?php if($my_row['CardGenderId'] == 2) { echo 'checked="checked"'; } ?> />&nbsp;Femenino<br />
					</div>
                        <span class="textHint">
                      &middot; G&eacute;nero o sexo del afiliado.</span>
                    </td>
                  </tr>
                  <?php 
				  $day   = "DD";
				  $month = "MM";
				  $year  = "AAAA";
				  if (trim($my_row['BirthDate']) <> "") { 
					  $day   = substr(trim($my_row['BirthDate']),6,2);
					  $month = substr(trim($my_row['BirthDate']),4,2);
					  $year  = substr(trim($my_row['BirthDate']),0,4);
				  }
				  ?>
                  <tr>
                    <td>
                    Fecha Nacimiento<br/>
         			D&iacute;a:&nbsp;
         			<input name="day" type="text" class="inputtextrequired" size="2" maxlength="2" value="<?php echo $day; ?>" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" onKeyUp="GoToNextField(this,2,'month');" />&nbsp;&nbsp;
                    Mes:&nbsp;
			        <input name="month" type="text" class="inputtextrequired" size="2" maxlength="2" value="<?php echo $month; ?>" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" onKeyUp="GoToNextField(this,2,'year');" />&nbsp;&nbsp;
                    A&ntilde;o:&nbsp;
			       <input name="year" type="text" class="inputtextrequired" size="4" maxlength="4" value="<?php echo $year; ?>" onKeyPress="return CheckNumbersOnly(this, event);" onfocus="if(this.value==this.defaultValue) this.value='';" />
                    <br />
                        <span class="textHint">
                      &middot; Fecha de nacimiento del afiliado.</span>
                     </td>
                  </tr>
                  <tr>
                    <td>
                    Email<br />
                    <input name="email" id="email" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['CardEmail']); ?>" /><br />
                    <span class="textHint">&middot; Email del afiliado.</span>
					</td>
                  </tr>
                  <tr>
                    <td>
                    Tel&eacute;fono<br />
                    <input name="phone" id="phone" type="text" class="inputtextrequired" size="20" onkeypress="return CheckCharactersOnly(event,numbers);" value="<?php echo trim($my_row['CardContactPhone']); ?>" /><br />
                    <span class="textHint"> &middot; N&uacute;mero de tel&eacute;fono.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Celular<br />
                    <input name="cellphone" id="cellphone" type="text" class="inputtext" size="20" onkeypress="return CheckCharactersOnly(event,numbers);" value="<?php echo trim($my_row['CardCellularPhone']); ?>" /><br />
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
                    <input name="doctorname" id="doctorname" type="text" class="inputtext" size="50" value="<?php echo trim($my_row['CardDoctor']); ?>" /><br />
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
								<span style="color:#ff0000;">No tienes privilegios para Editar!</span>
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
