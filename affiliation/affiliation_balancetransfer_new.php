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
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
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

		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'none';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'none'; }
		}
		$itemtype = strtoupper($itemtype);

		// Obtenemos las tarjetas involucradas
		$cardnumberfrom = '';
		if (isset($_GET['cardnumberfrom'])) {
			$cardnumberfrom = setOnlyText($_GET['cardnumberfrom']);
			//if (!is_numeric($cardnumberfrom)) { $cardnumberfrom = '0'; }
		}

		$cardnumberto = '';
		if (isset($_GET['cardnumberto'])) {
			$cardnumberto = setOnlyText($_GET['cardnumberto']);
			//if (!is_numeric($cardnumberto)) { $cardnumberto = ''; }
		}
		$transfertype = 'complete';
		if (isset($_GET['transfertype'])) {
			$transfertype = setOnlyLetters($_GET['transfertype']);
		}
		if ($itemtype == 'POINTS') { $transfertype = 'complete'; }
		


// ****************************************************************************************************************************************
// TBD: t = points o historial bonus
// TBD: validaciones de tarjeta?, nombre fecha nacimiento etc?.
// TBD: check hay params para la transferencia?, sino, pinta la forma con lo que hace falta.
// TBD: para transferir, hay que tener un auth o llave, sino no te deja, tanto del usuario como del check transfer
// TBD: transfer total o parcial?, diff: se bloquea el cardnumberfrom
// DONDE determino si es puntos o bonus?????


	// GET ACTION
		// Determinamos que acción tomaremos
		$action = 'new';
		
		// Si no hay error en parametros previos...
		if ($actionerrorid == 0) {
			
			// Hay ambas tarjetas?
			if ($cardnumberto !== '' && $cardnumberfrom !== '') {
				$action = 'check';
			}
			
		}
	
	
	// SET ACTIONS
		$transferbalance 	= 0;
		$transferauth 	 	= '0';
		$transferauthlocal 	= '0';

		// CHECK 
		// Verficamos si la transferencia es viable
		if ($action == 'check') {
			

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');
				
				$items = 0;
				$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemBalanceTransferManage
									'".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."',
									'".$itemtype."', 'check', 'crm',
									'0', '".$cardnumberto."',
									'0', '".$cardnumberfrom."',
									'complete', '".$actionauth."', '0',
									'1', '0', '0',
									'0';";
									//echo $query;
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows();
				if ($items > 0) {
					$my_row=$dbtransactions->get_row();
					$transferbalance 	= $my_row['CardBalance']; 
					$transferauth	 	= $my_row['TransferAuth']; 
					$transferauthlocal	= $my_row['TransferAuth']; 
					$actionerrorid 	 	= $my_row['Error']; 
				} else {
					$actionerrorid =  66; // if ($items > 0) { NOT FOUND
				}

		} 


		$TransferTypeDesc = "";
		if ($itemtype == "BONUS") 	{ $TransferTypeDesc = "Historial"; }
		if ($itemtype == "POINTS") 	{ $TransferTypeDesc = "Saldo"; }
		if ($itemtype == "RECORD") 	{ $TransferTypeDesc = "Historial / Actividades"; }
		if ($itemtype == "CARD") 	{ $TransferTypeDesc = "Tarjeta"; }
		if ($itemtype == "FULL") 	{ $TransferTypeDesc = "Tarjeta"; }


	// REFERER
		// Identificamos de donde viene... para regresarlo en caso de error
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
		
	
?>
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
                        // Si es una transferencia desde el inicio...
                        if ($action == 'new') { 
                        ?>
                        

							<SCRIPT type="text/javascript">
                            <!--
                            
                                function CheckRequiredFields() {
                                    var errormessage = new String();
                                    
                                    if(WithoutContent(document.orveefrmaffiliated.cardnumberfrom.value))
                                        { errormessage += "\n- Ingrese un número de tarjeta origen!."; }
                                    if(WithoutContent(document.orveefrmaffiliated.cardnumberto.value))
                                        { errormessage += "\n- Ingrese un número de tarjeta destino!."; }

                                    if(document.orveefrmaffiliated.cardnumberfrom.value == document.orveefrmaffiliated.cardnumberto.value)
                                        { errormessage += "\n- Las tarjetas origen y destino no pueden ser la misma!."; }
                            
                                    // Put field checks above this point.
                                    if(errormessage.length > 2) {
                                        alert('Para validar la transferencia, por favor: ' + errormessage);
                                        //document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Validación en proceso, por favor, espere un momento...</em><img src='../images/imageloading.gif' />";                                        
                                        return false;
                                        }
                                    //document.orveefrmuser.submit();
                                    return true;
                                } // end of function CheckRequiredFields()
                            
                            //-->
                            </SCRIPT>
    
                            <form action="index.php" method="get" name="orveefrmaffiliated" onsubmit="return CheckRequiredFields();">
                            <input name="m" type="hidden" value="affiliation" />
                            <input name="s" type="hidden" value="balancetransfer" />
                            <input name="a" type="hidden" value="new" />
                            <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                            <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                            <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                            
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
                                                <span class="textMedium">
                                                <?php echo $TransferTypeDesc; ?><br />
                                                Nueva Transferencia</span><br />
                                                </td>
                                              </tr>
                                            </table>
                                    
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>&nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                    Tarjeta Origen [-]<br />
                                    <input name="cardnumberfrom" id="cardnumberfrom" type="text" class="inputtextrequired" value="<?php echo $cardnumberfrom; ?>" />&nbsp;&nbsp;&nbsp;<img src="images/bulletremove.png" alt="Origen [-]" /><br />
                                        <span class="textHint">
                                        &middot; N&uacute;mero de tarjeta origen de la transferencia.
                                        </span>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                    Tarjeta Destino [+]<br />
                                    <input name="cardnumberto" id="cardnumberto" type="text" class="inputtextrequired" value="<?php echo $cardnumberto; ?>" />&nbsp;&nbsp;&nbsp;<img src="images/bulletadd.png" alt="Destino [+]" /><br />
                                        <span class="textHint">
                                        &middot; N&uacute;mero de tarjeta destino o beneficiaria de la transferencia.
                                        </span>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>&nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                    <div id="botonsubmit">
                                    <input name="submitbutton" id="submitbutton" type="submit" value="Validar" />
                                    </div>
                                    </td>
                                  </tr>
                                </table>
                                </form>
                              
                        <?php } // if ($action == 'new') ?>	

                        <?php if ($action == 'check') { ?>	

							<SCRIPT type="text/javascript">
                            <!--
                            
                                function CheckRequiredFields() {
                                    var errormessage = new String();
                                    
                                    if(WithoutContent(document.orveefrmaffiliated.cardnumberfrom.value))
                                        { errormessage += "\n- Ingrese un número de tarjeta origen!."; }
                                    if(WithoutContent(document.orveefrmaffiliated.cardnumberto.value))
                                        { errormessage += "\n- Ingrese un número de tarjeta destino!."; }

                                    if(document.orveefrmaffiliated.transfererror.value > 0)
                                        { errormessage += "\n- Verifique los datos ingresados e intente de nuevo!."; }
                            
                                    // Put field checks above this point.
                                    if(errormessage.length > 2) {
                                        alert('Para aplicar la transferencia, por favor: ' + errormessage);
                                        //document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Validación en proceso, por favor, espere un momento...</em><img src='../images/imageloading.gif' />";                                        
                                        return false;
                                        }
                                    //document.orveefrmuser.submit();
                                    return true;
                                } // end of function CheckRequiredFields()
                            
                            //-->
                            </SCRIPT>

                            <form action="index.php" method="get" name="orveefrmaffiliated" onsubmit="return CheckRequiredFields();">
                            <input name="m" type="hidden" value="affiliation" />
                            <input name="s" type="hidden" value="balancetransfer" />
                            <input name="a" type="hidden" value="add" />
                            <input name="t" type="hidden" value="<?php echo $itemtype; ?>" />
                            <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                            <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                            <input name="cardnumberfrom" type="hidden" value="<?php echo $cardnumberfrom; ?>" />
                            <input name="cardnumberto" type="hidden" value="<?php echo $cardnumberto; ?>" />
                            <input name="transferauth" type="hidden" value="<?php echo $transferauth; ?>" />
                            <input name="transfererror" type="hidden" value="<?php echo $actionerrorid; ?>" />
                             <input name="transferauthlocal" type="hidden" value="<?php echo $transferauthlocal; ?>" />
                              
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
                                                <span class="textMedium">
                                                <?php echo $TransferTypeDesc; ?><br />
                                                Nueva Transferencia</span><br />
                                                </td>
                                              </tr>
                                            </table>
                                    
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>&nbsp;
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>
                                    Tarjeta Origen [-]<br />
                                    <span class="textLarge">
                                    <img src="images/bulletremove.png" alt="Origen [-]" />&nbsp;&nbsp;&nbsp;
                                    <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumberfrom; ?>" target="_blank">
									<?php echo $cardnumberfrom; ?>
                                    </a>
                                    </span><br />
                                        <span class="textHint">
                                        &middot; N&uacute;mero de tarjeta origen de la transferencia.
                                        </span>
                                    </td>
                                  </tr>
                                  <?php if ($itemtype == "POINTS") { ?>
                                      <tr>
                                        <td>
                                        Saldo a Transferir<br />
                                        <span class="textLarge">
                                        <img src="images/bulletdown.png" alt="Saldo" />&nbsp;&nbsp;&nbsp;
                                        <?php echo $transferbalance; ?>
                                        </span><br />
                                            <span class="textHint">
                                            &middot; Saldo o puntos a transferir a la tarjeta destino.
                                            </span>
                                        </td>
                                      </tr>
                                  <?php } ?>
                                  <?php if ($itemtype == "BONUS") { ?>
                                      <tr>
                                        <td>
                                        Historial a Transferir<br />
                                        <span class="textLarge">
                                        <img src="images/bulletdown.png" alt="Historial" />&nbsp;&nbsp;&nbsp;
                                        <?php echo $transferbalance; ?>
                                        </span><br />
                                            <span class="textHint">
                                            &middot; Transacciones o compras a transferir a la tarjeta destino.
                                            </span>
                                        </td>
                                      </tr>
                                  <?php } ?>
                                  <tr>
                                    <td>
                                    Tarjeta Destino [+]<br />
                                    <span class="textLarge">
                                    <img src="images/bulletadd.png" alt="Origen [-]" />&nbsp;&nbsp;&nbsp;
                                    <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumberto; ?>" target="_blank">
                                    <?php echo $cardnumberto; ?>
                                    </a>
                                    </span><br />
                                        <span class="textHint">
                                        &middot; N&uacute;mero de tarjeta destino o beneficiaria de la transferencia.
                                        </span>
                                    </td>
                                  </tr>
                                  <?php if ($itemtype == "POINTS") { ?>
                                      <input name="transfertype" type="hidden" value="<?php echo $transfertype; ?>" />
                                  <?php } ?>
                                  <?php if ($itemtype == "BONUS") { ?>
                                      <tr>
                                        <td>
                                        Transferencia Tipo<br />
                                        <div class="fieldrequired">
											<input name="transfertype" id="transfertype" type="radio" value="complete" checked="checked"> COMPLETA <br />.
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<span style="font-style:italic;">
											&middot; Se transfiere el historial COMPLETO y la tarjeta ORIGEN es bloqueada de manera definitiva al final de la transferencia.
											</span><br /><br />
											<input name="transfertype" id="transfertype" type="radio" value="partial"> PARCIAL<br />
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<span style="font-style:italic;">
											&middot; Se transfiere el historial PARCIAL (solo historial libre para uso en bonificaci&oacute;n) y la tarjeta ORIGEN es bloqueada de manera definitiva al final de la transferencia.<br />
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp; El historial transferido es aquel que no haya sido ocupado en alguna bonificaci&oacute;n.
											</span><br /><br />
											<input name="transfertype" id="transfertype" type="radio" value="inversepartial"> INVERSA PARCIAL<br />
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<span style="font-style:italic;">
											&middot; Se transfiere el historial PARCIAL (solo historial libre para uso en bonificaci&oacute;n) y la tarjeta DESTINO es bloqueada de manera definitiva al final de la transferencia.<br />
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp; El historial transferido es aquel que ha sido ocupado en alguna bonificaci&oacute;n y se deja en la tarjeta origen aquel libre para uso en bonificaci&oacute;n.
											</span><br />
                                         </div>
                                            <span class="textHint">
                                            &middot; Tipo de transferencia a ejecutar.
                                            </span>
                                        </td>
                                      </tr>
                                  <?php } ?>
                                  <tr>
                                    <td>&nbsp;                                    
                                    </td>
                                  </tr>
                                  <?php if ($actionerrorid == 0) { ?>
                                      <tr>
                                        <td>
                                        <div id="botonsubmit">
                                        <input name="submitbutton" id="submitbutton" type="submit" value="Aplicar" />
                                        </div>
                                        </td>
                                      </tr>
                                  <?php } else { ?>
                                  

                                      <tr>
                                        <td style="padding: 5px 5px 5px 25px">
                                        
                                            <img src="images/iconresultwrong.png" />
                                            <br /><br />

                                            La TRANSFERENCIA NO pudo ser PROCESADA!.<br />
                                            <br />
                                            <?php switch($actionerrorid) { 
													case 265: 
                                                       echo "No hay saldo o transacciones para transferir.";
												  	   break; 
													default:
                                                       echo "Por favor, verifique sus datos y reintente.";
												  	   break; 
                                            	   }
										    ?>
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
            
                                        </td>
                                      </tr>     
                                      <tr>
                                        <td>
                                      
                                        <br /><br />
                                        <table class="botones2">
                                          <tr>
                                            <td class="botonstandard">
                                            <img src="images/bulletnew.png" />&nbsp;
                                            <a href="?m=affiliation&s=balancetransfer&a=new&t=<?php echo $itemtype; ?>">
                                            Nueva Transferencia</a>
                                            </td>
                                          </tr>
                                        </table>
                                        <br /><br />
                                
                                        </td>
                                      </tr>     
                                                              
                                  <?php } ?>
                            	</table>
                                </form>
                        <?php } // if ($action == 'check') ?>	
                        
       
                
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

