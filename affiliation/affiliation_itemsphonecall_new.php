<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* interactions_x.php
* 	Descripción de la función.
*
* @version 
*
*/

// --------------------------------------------------------
// TBD: Poner ubicación?, para ayuda referencia?.
// TBD: Poner todos los telefonos? y si hay selected pues ese se poner?
// TBD: Editar o reclasificar el telefono

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
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// Obtenemos el itemid, identificando el elemento a consultar
		$itemid = 0;
		if (isset($_GET['n'])) {
			$itemid = setOnlyNumbers($_GET['n']);
			if ($itemid == '') { $itemid = 0; }
			if (!is_numeric($itemid)) { $itemid = 0; }
		}

		// CARDNUMBER
		$cardnumber = '';
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyNumbers($_GET['cardnumber']);
			if (!is_numeric($cardnumber)) { $cardnumber = ''; }
		}

		// INTERACTIONID
		$interactionid = 1;
		if (isset($_GET['interactionid'])) {
			$interactionid = setOnlyNumbers($_GET['interactionid']);
			if ($interactionid == '') { $interactionid = 1; }
			if (!is_numeric($interactionid)) { $interactionid = 1; }
		}

		// PHONECALLTYPE
		$phonecalltype = 'inbound';
		if (isset($_GET['phonecalltype'])) {
			$phonecalltype = setOnlyLetters($_GET['phonecalltype']);
			if ($phonecalltype == '') { $phonecalltype = 'inbound'; }
		}
		
		// PHONENUMBER
		$phonenumber = '';
		$phonenumbertype = 'contactphone';
		if (isset($_GET['phonenumber'])) {
			$phonenumber = setOnlyNumbers($_GET['phonenumber']);
			//if ($phonenumber == '') { $actionerrorid = 2; }
			//if (!is_numeric($phonenumber)) { $actionerrorid = 2; }
		}		

		// PHONECALLSOURCE??????????????????????????????, para generar una nueva llamada?, IF ITEMID = 0 get random!!!
		$phonecallsource = 'affiliated';
		if (isset($_GET['t'])) {
			$phonecallsource = setOnlyText($_GET['t']);
			if ($phonecallsource == '') { $phonecallsource = 'affiliated'; }
			if (!is_numeric($phonenumber)) { $actionerrorid = 2; }
		}		
		


	// GET INTERACTION
		$phonecalltype		= '';
		$interactionname 	= ''; 
		$interactiontype 	= ''; 
		$interactioncontent	= ''; 
		if ($interactionid == '0') { $interactionid = '1'; }

				$items = 0;
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_InteractionsPhoneCallManage
									'".$_SESSION[$configuration['appkey']]['userid']."', 
									'".$configuration['appkey']."', 
									'content', 
									'0',
									'',
									'',
									'PHONE', 
									'".$interactionid."';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();

					$interactionname	= $my_row['InteractionName']; 
					$interactiontype 	= $my_row['InteractionSubType'];
					$interactioncontent = trim($my_row['InteractionContent']);
					$phonecalltype 		= $my_row['InteractionSubType'];
				
				} else {
					$actionerrorid =  66; // if ($items > 0) { NOT FOUND
				}
				


	// GET RECORD
		$cardnumber			= '0';
		$affiliationcard 	= '0'; 
		$affiliationname 	= ''; 
		$affiliationstatus  = '';
		
		
			// ------------------------------
			// OUTBOUND RANDOM CARDNUMBER @ LIST
			// ------------------------------
				// GET new record from list
				if ($itemid == 0 && $phonecalltype == 'outbound' && $actionerrorid == 0) { 

					// OUTBOUND LIST					
						$items = 0;
						$query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemsOutboundList
										'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
										'list', '1', '1', '', '', 'PHONE';";
						$dbconnection->query($query);
						$items = $dbconnection->count_rows();
						if ($items > 0) {
							$my_row=$dbconnection->get_row();
							$itemid 	 = $my_row['CardAffiliationId']; 
							$cardnumber	 = $my_row['CardNumber']; 
						} else {
							$itemid 	 = 0; 
							$cardnumber	 = ''; 
						}
					
				}


		// Si el ItemId es válido, consultamos a la base de datos...
		if ($itemid > 0) {
			
				$items = 0;
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem 
									'".$itemid."', '0';";
				$dbconnection->query($query);
				$items = $dbconnection->count_rows();
				if ($items > 0) {
					$my_row=$dbconnection->get_row();

					$cardnumber		 	 = $my_row['CardNumber']; 
					$affiliationcard 	 = $my_row['CardNumber'];
					$affiliationname	 = $my_row['CardName'];
					$phonenumber		 = setOnlyNumbers($my_row['CardContactPhone'])." / ".setOnlyNumbers($my_row['CardCellularPhone']);
					$phonenumbertype	 = 'contactphone';
				
				} else {
					$actionerrorid =  66; // if ($items > 0) { NOT FOUND
				}

		} else {
			if ($actionerrorid == 0) { $actionerrorid =  66; } // if ($itemid > 0) { NOT FOUND
		}



	// INTERACTION CONTENT add params to interaction content
		if ($interactioncontent !== '') {
			$interactioncontent .= "?n=".$itemid."&cardnumber=".$cardnumber."&interactionid=".$interactionid."";
		}


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
	
?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();

		if(document.orveefrmaffiliated.phonestatusid.value == 1)
			{  


		if(document.orveefrmaffiliated.phonecalltype.value == 'outbound' && WithoutSelectionValue(document.orveefrmaffiliated.phonestatusid))
			{ errormessage += "\n- Seleccione una calificación de llamada!."; }

		var interactionreferences = document.getElementsByName('interactionreference[]');
		if(document.orveefrmaffiliated.phonecalltype.value == 'inbound' && NoneWithCheckArray(interactionreferences))
			{ errormessage += "\n- Seleccione una referencia de llamada!."; }

	  <?php if ($interactionid == 4) { ?>
	  			
			if (NoneWithCheck(document.orveefrmaffiliated.sesionok)){ 
				errormessage += "\n- Seleccione si se realizó la sesión!.";
			}else{
				
				if (document.orveefrmaffiliated.sesionok.value == 1) {
					
					if (NoneWithCheck(document.orveefrmaffiliated.puntualidad))
						{ errormessage += "\n- Califique Puntualidad y presentación del educador!."; }

					if (NoneWithCheck(document.orveefrmaffiliated.dominio))
						{ errormessage += "\n- Califique Dominio de los temas vistos en su sesión educativa!."; }
					
					if (NoneWithCheck(document.orveefrmaffiliated.utilidad))
						{ errormessage += "\n- Califique Utilidad de la información que el educador le proporcionó!."; }
					
					if (NoneWithCheck(document.orveefrmaffiliated.expectativas)){ 
						errormessage += "\n- Seleccione Que tanto cubrió sus expectativas la sesión educativa!."; 
					}else{
						
						if (document.orveefrmaffiliated.expectativas.value == 1) {
							
							if (NoneWithCheck(document.orveefrmaffiliated.expectativamotivo)){ 
								errormessage += "\n- Seleccione Motivo por el cual no cubrió sus expectativas!"; 
							}else{
								
								if (document.orveefrmaffiliated.expectativamotivo.value == 5) {
									if(WithoutContent(document.orveefrmaffiliated.expectativaotro.value))
										{ errormessage += "\n- Ingrese Otro motivo por el cual no cubrió sus expectativas!."; }
								}
							}
						}
					}

				}else{
					
					if (NoneWithCheck(document.orveefrmaffiliated.sesionmotivo)){ 
						errormessage += "\n- Seleccione el motivo por el que no se llevó a cabo el entrenamiento!."; 
					}else{
						
						if (document.orveefrmaffiliated.sesionmotivo.value == 3) {
							
							if(WithoutContent(document.orveefrmaffiliated.sesionmotivootro.value))
								{ errormessage += "\n- Ingrese otro motivo por el cual no se realizó el entrenamiento!."; }
						}
					}
					
					if (NoneWithCheck(document.orveefrmaffiliated.reagendar))
						{ errormessage += "\n- Seleccione si se requiere reagendar la cita!."; }
					
				}
				}
			}
			
						
	  
	  <?php } ?>
	  
	  <?php if ($interactionid == 5) { ?>
	  			
			if (NoneWithCheck(document.orveefrmaffiliated.problemas)){ 
				errormessage += "\n- Seleccione si el paciente reporta problemas!.";
			}			
	  
	  <?php } ?>

			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar la llamada, por favor: ' + errormessage);
			
			return false;
			}
		//document.orveefrmuser.submit();
		return true;
	} // end of function CheckRequiredFields()

//-->
</SCRIPT>

		<script type="text/javascript">
            jQuery(document).ready(function() {
                 jQuery("abbr.timeago").timeago();
            });
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
                <input name="s" type="hidden" value="itemsphonecall" />
                <input name="a" type="hidden" value="add" />
                <input name="n" id="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="cardnumber" id="cardnumber" type="hidden" value="<?php echo $cardnumber; ?>" />
                <input name="actionauth" id="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="phonecalltype" id="phonecalltype" type="hidden" value="<?php echo $phonecalltype; ?>" />
                
             <table border="0" cellspacing="0" cellpadding="10" width="100%">
                 <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imageaffiliated.gif" class="imagenaffiliationuser" alt="Affiliated Status" title="Affiliated Status" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                <?php echo $cardnumber; ?><br />
                                <span class="textSmall"><?php echo $affiliationname; ?><br /></span>
                                Nueva Llamada <?php echo strtoupper($interactiontype); ?>
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                                          
					<?php 
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
  
                          <tr>
                            <td>
             				
                                <table style="border-collapse:collapse;width:90%;margin:auto;">
                                  <tr>
                                    <td align="center" style="background-color:#ffffff;font-weight:bold;color:#FFFFFF;font-size:14px;width:15%">
                                    <img src="images/imageinteractions.png" class="imagesection" alt="Último Contacto" title="Último Contacto" />
                                    </td>
                                    <td align="left" style="padding:10px 10px 10px 10px;background-color:#f0f0f0;border-left: 1px solid #ADB1BD;width:85%">
                                    
                                   	<?php

										$items = 0;
                                        $query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_InteractionsPhoneCallManage
                                                            '".$_SESSION[$configuration['appkey']]['userid']."', 
                                                            '".$configuration['appkey']."', 
                                                            'lastcontact', 
                                                            '".$itemid."',
                                                            '".$cardnumber."',
                                                            '".$phonenumber."',
                                                            'PHONE', 
                                                            '".$interactionid."';";
										$dbconnection->query($query);
										while ($my_row=$dbconnection->get_row()) {
											$items = $items + 1;
											?>
						
											<span style='font-weight:bold;font-size:11px;'>
                                            <?php echo $my_row['PhoneCallDate']; ?>
                                            </span>&nbsp;&nbsp;&nbsp;
                                                <span style="font-size:9px;font-style:italic;">
                                                * <abbr class="timeago" title="<?php echo $my_row['PhoneCallTimeAgo']; ?>">
                                                <?php echo $my_row['PhoneCallTimeAgo']; ?>
                                                </abbr>
                                                </span>
                                            <br />
                                            <?php echo $my_row['PhoneCallStatus']." @ ".$my_row['PhoneNumber']; ?>
                                            &nbsp;&nbsp;&nbsp;
                                            <span style="font-style:italic;">
                                            * Motivo: 
                                            <?php echo $my_row['InteractionName']." [".strtoupper($my_row['PhoneCallType'])."]"; ?>
                                            </span>
                                            <br />
                                            Notas: <em><?php echo $my_row['PhoneCallNotes']; ?></em>
                                            
                                            <?php
										
										} 
										if ($items == 0) {
											?>
											<span style='font-size:14px;font-style:italic;'>
                                            Sin Contacto Reciente
                                            </span>
                                            <?php
										}
                                    
                                   	?>

                                    </td>
                                  </tr>
                                </table>
                                                            
                            </td>
                          </tr>
  
                          <tr>
                            <td>
                            Llamada Motivo / Campa&ntilde;a<br />
                            <span class="textMedium">
                            <?php echo $interactionname." [".strtoupper($interactiontype)."]"; ?>
                            <?php if ($interactioncontent !== '') { ?>
                            <!--&nbsp;<a href="<?php echo $interactioncontent; ?>" target="_blank" title="Ver Script">-->
                            &nbsp;<a href="affiliation/getInteractionPhoneContent.php?n=<?php echo $itemid; ?>&cardnumber=<?php echo $cardnumber; ?>&interactionid=<?php echo $interactionid; ?>" target="_blank" title="Ver Script">
                            <img src="images/bulletlist2.png" alt="Ver Script" title="Ver Script" />
                            <img src="images/bulletright.png" alt="Ver Script" title="Ver Script" />
                            </a>
                            <?php } ?>
                            </span>
                            <br />
                            <span class="textHint"> &middot; Motivo o guion de la llamada.</span>
                            <input name="interactionid" id="interactionid" type="hidden" value="<?php echo $interactionid; ?>" />
                            </td>
                         </tr>
                                                   
  
  				<!-- INBOUND: begin -->	
						<?php if ($phonecalltype == 'inbound') {  ?>	
                        
                          <tr>
                            <td>
                            Tel&eacute;fono<br />
                            <input name="phonenumber" id="phonenumber" type="text" class="inputtextrequired" size="20" onkeypress="return CheckCharactersOnly(event,numbers);" /><br />
                            <span class="textHint">
                             &middot; N&uacute;mero de tel&eacute;fono de la tarjeta.<br />
                                 <div class="fieldrequired">
                                 <input name="phonenumbertype" type="radio" value="contactphone" <?php if ($phonenumbertype == 'contactphone') { echo 'checked="checked"'; } ?> />&nbsp;Contacto<br />
                                 <input name="phonenumbertype" type="radio" value="cellphone" <?php if ($phonenumbertype == 'cellphone') { echo 'checked="checked"'; } ?> />&nbsp;Celular<br />
                                 </div>
                            </span>
                            <input name="phonestatusid" id="phonestatusid" type="hidden" value="1" />                          
                            </td>
                          </tr>
                           <tr>
                            <td>
                            Referencia<br />
                            <div class="fieldrequired">
                            <?php
                                $query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 'PhoneCallReferences','';";
                                $dbconnection->query($query);
                                while($my_row=$dbconnection->get_row()){ 
                                    if ($my_row['ItemIsSelected'] == 1) {
                                        echo "<input name='interactionreference[]' id='interactionreference[]' type='checkbox' value='".$my_row['ItemId']."' checked='checked'>";
                                        echo "&nbsp;".$my_row['Item']."<br />";
                                    } else {
                                        echo "<input name='interactionreference[]' id='interactionreference[]' type='checkbox' value='".$my_row['ItemId']."'>";
                                        echo "&nbsp;".$my_row['Item']."<br />";
                                    }
                                }
                            ?>
                            </div>
                            <span class="textHint"> &middot; Referencia de la llamada.</span>
                            </td>
                         </tr>                                   
   
  				<!-- INBOUND: end -->	
						<?php } else {  ?>	
    			<!-- OUTBOUND: begin -->	
                
							<?php
                            
								// PHONENUMBER OK?
								$phonenumberstatus = '0';
								if (strlen($phonenumber) < 7) {
									$phonenumberstatus = '2';
								}
						
                            ?>
                      

                          <tr>
                            <td>
                            Tel&eacute;fono<br />
                            <span class="textLarge" style="vertical-align:middle;">
                            <span style="font-style:italic;">
                            <?php if ($phonenumberstatus !== '0') { ?>
                            	<span style="color:#FF0000;">
	                            <?php echo $phonenumber; ?>
                            	<img src="images/bulletcancel.png" alt="Teléfono Inválido" title="Teléfono Inválido" />
                                </span>
                            <?php } else { ?>
	                            <?php echo $phonenumber; ?>
                            <?php } ?>
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            
                                        <select name="phonestatusid" id="phonestatusid" class="selectrequired">
                                            <option value="">[Calificación Llamada]</option>
                                            <?php
                                                $query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 
																			'PhoneCallStatus','".$phonenumberstatus."';";
                                                $dbconnection->query($query);
                                                while($my_row=$dbconnection->get_row()){ 
                                                    if ($my_row['ItemIsSelected'] == 1) {
                                                        echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                                        echo "&nbsp;".$my_row['Item']."</option>";
                                                    } else {
                                                        echo "<option value='".$my_row['ItemId']."'>";
                                                        echo "&nbsp;".$my_row['Item']."</option>";
                                                    }
                                                }
                                            ?>
                                        </select>                            
                            </span>
                            <span class="textHint">
                             &middot; N&uacute;mero de tel&eacute;fono de la tarjeta.<br />
                                 <div class="fieldrequired">
                                 <input name="phonenumbertype" type="radio" value="contactphone" <?php if ($phonenumbertype == 'contactphone') { echo 'checked="checked"'; } ?> />&nbsp;Contacto<br />
                                 <input name="phonenumbertype" type="radio" value="cellphone" <?php if ($phonenumbertype == 'cellphone') { echo 'checked="checked"'; } ?> />&nbsp;Celular<br />
                                 </div>
                            </span>
                                <input name="phonenumber" type="hidden" value="<?php echo $phonenumber; ?>" />
                            </td>
                          </tr>
                          					  
						  
						  <?php if ($interactionid == 4) { ?>
						  
							  
							  <tr>
                                <td>
                                <div style="padding-left:40px;">
                                <br />
									¿Se realiz&oacute; la sesi&oacute;n educativa?
									<div class="fieldrequired">
										<input name="sesionok" type="radio" value="1" />&nbsp;SI
										<input name="sesionok" type="radio" value="0" />&nbsp;NO
									</div>
									<span class="textHint"> &middot; Reagendar su cita ?.</span>
                                </div>
                                </td>
                              </tr>
						  
							  <tr>
                                <td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									<div style="padding-left:40px;">
										<b>Paciente indica que NO se realiz&oacute; la sesi&oacute;n<b/>
									</div>
									</td>
								</tr>
								</table>
                                </td>
                              </tr>
								
							  <tr>
                                <td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									<div style="padding-left:40px;" >
										¿Podr&iacute;a indicarnos el motivo por el que no se llev&oacute; a cabo el entrenamiento?
										<div class="fieldrequired">
											<?php
												$query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																					 'EducacionSesionMotivos','';";
												$dbconnection->query($query);
												while($my_row=$dbconnection->get_row()){ 
													if ($my_row['ItemIsSelected'] == 1) {
														echo "<input name='sesionmotivo' id='sesionmotivo' type='radio' value='".$my_row['ItemId']."' checked='checked'>";
														echo "&nbsp;".$my_row['Item']."<br />";
													} else {
														echo "<input name='sesionmotivo' id='sesionmotivo' type='radio' value='".$my_row['ItemId']."'>";
														echo "&nbsp;".$my_row['Item']."<br />";
													}
												}
											?>
										</div>
										<br />
										<table style="padding-left:25px;">
										<tr>
											<td>
											<div>
												Otros<br />
												<input name="sesionmotivootro" id="sesionmotivootro" type="text" class="inputtext" size="30">
											</div>
											<span class="textHint"> &middot; Motivo por el que no se realiz&oacute; la sesi&oacute;n.</span>
											</td>
										</tr>
										</table>
									</div>
									</td>
								</tr>
								</table>
                                </td>
                              </tr>
							  
							  <tr>
                                <td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									<div style="padding-left:40px;">
										¿Requiere Usted que volvamos a agendar su cita?
										<div class="fieldrequired">
											<input name="reagendar" type="radio" value="1" />&nbsp;SI
											<input name="reagendar" type="radio" value="0" />&nbsp;NO
										</div>
										<span class="textHint"> &middot; Reagendar su cita ?.</span>
									</div>
									</td>
								</tr>
								</table>
                                </td>
                              </tr>
						  
							  <tr>
                                <td>
								<div style="padding-left:40px;">
								<br />
									<b>Paciente indica que SI se realiz&oacute; la sesi&oacute;n<b/>
								</div>
                                </td>
                              </tr>
						  
                              <tr>
                                <td>
                                <div style="padding-left:40px;">
									¿En una escala del 1 al 10, donde el 10 es la calificaci&oacute;n m&aacute;xima, como evaluar&iacute;a la puntualidad y presentaci&oacute;n del educador para iniciar y terminar su sesi&oacute;n educativa?<br />
									<div class="fieldrequired">
										<input name="puntualidad" type="radio" value="1" />&nbsp;1
										<input name="puntualidad" type="radio" value="2" />&nbsp;2
										<input name="puntualidad" type="radio" value="3" />&nbsp;3
										<input name="puntualidad" type="radio" value="4" />&nbsp;4
										<input name="puntualidad" type="radio" value="5" />&nbsp;5
										<input name="puntualidad" type="radio" value="6" />&nbsp;6
										<input name="puntualidad" type="radio" value="7" />&nbsp;7
										<input name="puntualidad" type="radio" value="8" />&nbsp;8
										<input name="puntualidad" type="radio" value="9" />&nbsp;9
										<input name="puntualidad" type="radio" value="10" />&nbsp;10
									</div>
									<span class="textHint"> &middot; Puntualidad y presentaci&oacute;n del educador.</span>
                                </div>
                                </td>
                              </tr>
							  <tr>
                                <td>
                                <div style="padding-left:40px;">
                                <br />
									¿En una escala del 1 al 10 como evaluar&iacute;a el dominio de los temas vistos en su sesi&oacute;n educativa por parte del  educador?<br />
									<div class="fieldrequired">
										<input name="dominio" type="radio" value="1" />&nbsp;1
										<input name="dominio" type="radio" value="2" />&nbsp;2
										<input name="dominio" type="radio" value="3" />&nbsp;3
										<input name="dominio" type="radio" value="4" />&nbsp;4
										<input name="dominio" type="radio" value="5" />&nbsp;5
										<input name="dominio" type="radio" value="6" />&nbsp;6
										<input name="dominio" type="radio" value="7" />&nbsp;7
										<input name="dominio" type="radio" value="8" />&nbsp;8
										<input name="dominio" type="radio" value="9" />&nbsp;9
										<input name="dominio" type="radio" value="10" />&nbsp;10
									</div>
									<span class="textHint"> &middot; Dominio de los temas vistos en su sesi&oacute;n educativa.</span>
                                </div>
                                </td>
                              </tr>
							  <tr>
                                <td>
                                <div style="padding-left:40px;">
                                <br />
									¿En una escala del 1 al 10, C&oacute;mo evaluar&iacute;a la utilidad de la informaci&oacute;n que el educador le proporcion&oacute; en su sesi&oacute;n educativa?
									<div class="fieldrequired">
										<input name="utilidad" type="radio" value="1" />&nbsp;1
										<input name="utilidad" type="radio" value="2" />&nbsp;2
										<input name="utilidad" type="radio" value="3" />&nbsp;3
										<input name="utilidad" type="radio" value="4" />&nbsp;4
										<input name="utilidad" type="radio" value="5" />&nbsp;5
										<input name="utilidad" type="radio" value="6" />&nbsp;6
										<input name="utilidad" type="radio" value="7" />&nbsp;7
										<input name="utilidad" type="radio" value="8" />&nbsp;8
										<input name="utilidad" type="radio" value="9" />&nbsp;9
										<input name="utilidad" type="radio" value="10" />&nbsp;10
									</div>
									<span class="textHint"> &middot; Utilidad de la informaci&oacute;n que el educador le proporcion&oacute;.</span>
                                </div>
                                </td>
                              </tr>
							  <tr>
                                <td>
                                <div style="padding-left:40px;">
                                <br />
									¿Su sesi&oacute;n educativa, que tanto cubri&oacute; sus expectativas?
									<div class="fieldrequired">
										<?php
											$query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																				 'EducacionExpectativas','';";
											$dbconnection->query($query);
											while($my_row=$dbconnection->get_row()){ 
												if ($my_row['ItemIsSelected'] == 1) {
													echo "<input name='expectativas' id='expectativas' type='radio' value='".$my_row['ItemId']."' checked='checked'>";
													echo "&nbsp;".$my_row['Item']."<br />";
												} else {
													echo "<input name='expectativas' id='expectativas' type='radio' value='".$my_row['ItemId']."'>";
													echo "&nbsp;".$my_row['Item']."<br />";
												}
											}
										?>
									</div>
									<span class="textHint"> &middot; Que tanto cubri&oacute; sus expectativas la sesi&oacute;n educativa.</span>
                                </div>
                                </td>
                              </tr>
							  <tr>
                                <td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									<div style="padding-left:40px;">
									<br />
										¿Por qu&eacute; no cubri&oacute; sus expectativas?
										<div class="fieldrequired">
											<?php
												$query = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements
																					 'EducacionExpectativaMotivos','';";
												$dbconnection->query($query);
												while($my_row=$dbconnection->get_row()){ 
													if ($my_row['ItemIsSelected'] == 1) {
														echo "<input name='expectativamotivo' id='expectativamotivo' type='radio' value='".$my_row['ItemId']."' checked='checked'>";
														echo "&nbsp;".$my_row['Item']."<br />";
													} else {
														echo "<input name='expectativamotivo' id='expectativamotivo' type='radio' value='".$my_row['ItemId']."'>";
														echo "&nbsp;".$my_row['Item']."<br />";
													}
												}
											?>
										</div>
										<br />
										<table style="padding-left:25px;">
										<tr>
											<td>
											<div>
												Otros<br />
												<input name="expectativaotro" id="expectativaotro" type="text" class="inputtext" size="30">
											</div>
											<span class="textHint"> &middot; Motivo por el cual no cubri&oacute; sus expectativas.</span>
											</td>
										</tr>
										</table>	
									</div>
									</td>
								</tr>
								</table>
								</td>
                              </tr> 
							  
                          <?php } ?>
						  
						  
						  
						  
						  <?php if ($interactionid == 5) { ?>
						  
							  <tr>
                                <td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									<br />
										¿Paciente reporta problemas?
										<div class="fieldrequired">
											<input name="problemas" type="radio" value="1" />&nbsp;SI
											<input name="problemas" type="radio" value="0" />&nbsp;NO
										</div>
										<span class="textHint"> &middot; Tuvo algun inconveniente ?.</span>
									</td>
								</tr>
								</table>	
                                </td>
                              </tr>
							  
							  <tr>
								<td>
								<table style="padding-left:25px;">
								<tr>
									<td>
									Comentarios<br />
									<textarea name="interactionnotes" id="interactionnotes" cols="60" rows="4" title="Comentarios" maxlength="250" style="font-size:10px;"></textarea><br />
									<span class="textHint"> &middot; Comentarios u observaciones acerca del problema.</span>                            
									</td>
								</tr>
								</table>
								</td>
							  </tr>
							  
                          <?php } ?>
						  
						  
						  
						  
                        
    			<!-- OUTBOUND: end -->	
						<?php } ?>	
                        
                       <tr>
                        <td>
                        Notas<br />
                        <textarea name="phonecallnotes" id="phonecallnotes" cols="80" rows="5" title="Notas Llamada" maxlength="250" style="font-size:12px;"></textarea><br />
                        <span class="textHint"> &middot; Notas u observaciones de la llamada.</span>                            
                        </td>
                      </tr>

                      <tr>
                        <td>
                        <div id="botonsubmit">
                        <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                        </div>
                        </td>
                      </tr>
                  

					<?php }
                    if ($actionerrorid > 0) { 
					?>	
                          
                          <tr>
                            <td>
                            
                                    <img src="images/iconresultwrong.png" /><br /><br />
                                    La Llamada NO pueder ser PROCESADA!.<br />
                                    <br />
                                    Por favor, verifique sus datos y reintente.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />

                            </td>
                          </tr>     
					<?php } ?>	
                </table>
				</form>

                    	<br /><br />

        </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">
        
					<!-- Incluimos el sidebar del modulo-->
                    <?php 
					// Armamos dinamicamente el nombre del sidebar
					//$sidebarfile = $module."_sidebar.php";
					$sidebarfile = $module."_phonecall_sidebar.php";
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
