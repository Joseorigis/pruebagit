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

		// Obtenemos el itemtype, el tipo de elemento a consultar
		$itemtype = 'points';
		if (isset($_GET['t'])) {
			$itemtype = setOnlyLetters($_GET['t']);
			if ($itemtype == '') { $itemtype = 'none'; }
		}
		$itemtype = strtoupper($itemtype);

		// Obtenemos las tarjetas involucradas
		$cardnumber = '';
		if (isset($_GET['cardnumber'])) {
			$cardnumber = setOnlyNumbers($_GET['cardnumber']);
			if (!is_numeric($cardnumber)) { $cardnumber = '0'; }
		}


	// GET RECORD
		$affiliationid		= $itemid;
		//$cardnumber			= "0";
		$affiliationcard 	= "0"; 
		$affiliationname 	= ""; 

		// Si el ItemId es válido, consultamos a la base de datos...
		if ($itemid > 0) {
			
			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');
			
				$items = 0;
				$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItem
									'0', '".$cardnumber."';";
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows();
				if ($items > 0) {
					$my_row=$dbtransactions->get_row();
					$affiliationid	 	= $my_row['CardAffiliationId']; 
					$affiliationcard 	= $my_row['CardNumber']; 
					$cardnumber		 	= $my_row['CardNumber']; 
					$affiliationname 	= $my_row['CardName']; 
				} else {
					$actionerrorid =  66; // if ($items > 0) { NOT FOUND
				}

		} else {
			if ($actionerrorid == 0) { $actionerrorid =  66; } // if ($itemid > 0) { NOT FOUND
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
	
	if(WithoutContent(document.orveefrmaffiliated.points.value))
		{ errormessage += "\n- Ingrese los puntos a transferir!."; }

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
                <input name="s" type="hidden" value="balancepoints" />
                <input name="a" type="hidden" value="add" />
                <input name="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="cardnumber" type="hidden" value="<?php echo $cardnumber; ?>" />
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
                                <?php echo $affiliationcard; ?><br />
                                <span class="textSmall"><?php echo $affiliationname; ?><br /></span>
                                Nueva Transferencia PUNTOS
                                </span><br />
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
                        Puntos<br />
                        <input name="points" id="points" type="text" class="inputtextrequired" size="10" onkeypress="return CheckCharactersOnly(event,numbers);" /><br />
                        <span class="textHint">
                        &middot; Puntos a abonar a la tarjeta.<br />
                        &middot; Ingresar n&uacute;meros enteros, no decimales.<br />
                        </span>
                        </td>
                      </tr>
                      <tr>
                        <td>
                        Referencia<br />
                        <textarea name="pointsreference" id="pointsreference" cols="80" rows="5"></textarea><br />
                        <span class="textHint"> &middot; Referencia o comentarios sobre la transferencia.</span>
                        </td>
                      </tr>

                      <tr>
                        <td>
                        <div id="botonsubmit">
							<?php if ($actionerrorid == 0) { ?>
                                <input name="submitbutton" id="submitbutton" type="submit" value="Aplicar" />
                            <?php } else { ?>
                                <input name="submitbutton" id="submitbutton" type="submit" value="Aplicar" disabled />
                            <?php }  ?>
                        </div>
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
