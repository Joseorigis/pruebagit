<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* page.php
* 	Descripci�n de la funci�n.
*
* @version 
*
*/

// HEADERS
	// Verificamos si la p�gina es llamada dentro de otra, para invocar los headers
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=ISO-8859-15');
		// HTML headers
		header ('Expires: Sat, 01 Jan 2000 00:00:01 GMT'); //Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); //always modified
		header ('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); //HTTP/1.1
		header ('Pragma: no-cache');	// HTTP/1.0
	}

// SCRIPT
	// Obtengo el nombre del script en ejecuci�n
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
		// ERROR MESSAGE
		$errormessage = "";


	// REQUEST SOURCE VALIDATION
		$requestsource = getRequestSource();
		if ($requestsource !== 'domain' && $requestsource !== 'page') {
			$actionerrorid = 10;
			include_once("accessdenied.php"); 
			exit();
		}


	// PARAMETER VALIDATION
		// itemid ... in case off
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == '') { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = 0; }
			}	

		// itemtype
			$itemtype = 'warnings';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'warnings'; }
			}
			$itemtype = strtolower($itemtype);

		// ruletype
			$ruletype = 'warnings';
			if (isset($_GET['t'])) {
				$ruletype = setOnlyLetters($_GET['t']);
				if ($ruletype == '') { $ruletype = 'warnings'; }
			}
			$ruletype = strtolower($ruletype);

		// rulemonitor
			$rulemonitor = $_SESSION[$configuration['appkey']]['email'];
		// rulename
			$rulename = "";


			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');


		
				$records = 0;
				$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_Rules".$ruletype."Manage
									'".$_SESSION[$configuration['appkey']]['userid']."', 
									'".$configuration['appkey']."',
									'view', 
									'".$ruletype."', 
									'".$itemid."';";
				$dbtransactions->query($query);
				$records = $dbtransactions->count_rows();
				if ($records > 0) {
					$my_row=$dbtransactions->get_row();
					
					$itemid			 	= $my_row['RuleId']; 
					$rulename			= $my_row['RuleName']; 
					$rulemonitor		= $my_row['WarningDistributionList']; 
					$actionerrorid 		= $my_row['Error']; 

				} else {
					$actionerrorid = 66;
				}
									

	// REFERER
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }

?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		var distributionlist = document.orveefrmrule.ruledistributionlist.value;
		
		//if (distributionlist.length > 1000)
		//	{ errormessage += "\n- La lista de distribuci�n debe ser de menor tama�o!."; }

	
		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para editar la regla, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliaci�n en proceso, por favor, espere un momento...</em>";
			
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

                   			<?php  if ($records == 0) { ?>
                            
                                    <table class="tablemessage">
                                      <tr>
                                        <td bgcolor="#FF0000">&nbsp;</td>
                                        <td bgcolor="#F0F0F0">			
                                                <br />
                                                <img src="images/security_firewall_off.png" alt="Error" />
                                                <br />
                                                <br />
                                                <span class="textMedium">Oooops!
                                                <br />
                                                La regla no fue encontrada!.</span>
                                                <br />
                                                <br />
                                               	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                Por favor, valida la informaci&oacute;n que ingresaste e intenta nuevamente.
                                                <br />
                                                <br />
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src="images/bulletleft.png" />&nbsp;
                                                <a href="<?php echo $referer; ?>" title="Regresar">Regresar</a><br />
                                                <br />
                    
                                        </td>
                                      </tr>
                                    </table>
                                    
                   			<?php  } else { ?>

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="warnings" />
                <input name="a" type="hidden" value="update" />
                <input name="t" type="hidden" value="warnings" />
                <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <input name="connectionid" type="hidden" value="1" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom" style="border-bottom:5px solid #<?php echo $my_row['WarningColor']; ?>;">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
                                <span class="textMedium">
                                Regla <?php echo $ruletype; ?><br />
                                <?php echo $rulename; ?>
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Status<br />
                    <span class="textMedium"><em><?php echo $my_row['RulePublishStatus']; ?></em></span><br />
                    <span class="textHint"> &middot; Status actual de la regla.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Regla Negocio<br />
                    <span class="textMedium">
                    	<em><?php echo $my_row['RuleSubTypeDesc']; ?></em><br />
                    	<em><?php echo $my_row['WarningOperationDesc']; ?> 
                        	<?php echo $my_row['WarningOperationUnits']; ?> 
                        	<?php echo $my_row['WarningObjectDesc']; ?> 
                        	<?php echo $my_row['WarningScheduleDesc']; ?> 
                        	</em><br />
                        <span class="textSmall"> &middot; <em><?php echo $my_row['RuleDescription']; ?></em></span>
                    </span><br />
                    <span class="textHint"> 
                    &middot; Reglas o configuraciones de la alarma.<br />
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
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                
						<?php } ?>
                        
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

