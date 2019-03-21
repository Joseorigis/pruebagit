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
			$cardnumberfrom = setOnlyNumbers($_GET['cardnumberfrom']);
			if (!is_numeric($cardnumberfrom)) { $cardnumberfrom = '0'; }
		}

		$cardnumberto = '';
		if (isset($_GET['cardnumberto'])) {
			$cardnumberto = setOnlyNumbers($_GET['cardnumberto']);
			if (!is_numeric($cardnumberto)) { $cardnumberto = ''; }
		}

		$transferauth = '';
		if (isset($_GET['transferauth'])) {
			$transferauth = setOnlyText($_GET['transferauth']);
		}

		$transferauthlocal = '';
		if (isset($_GET['transferauth'])) {
			$transferauthlocal = setOnlyText($_GET['transferauth']);
		}

		$transfertype = 'complete';
		if (isset($_GET['transfertype'])) {
			$transfertype = setOnlyLetters($_GET['transfertype']);
		}


// ****************************************************************************************************************************************
// TBD: t = points o historial bonus
// TBD: validaciones de tarjeta?, nombre fecha nacimiento etc?.
// TBD: check hay params para la transferencia?, sino, pinta la forma con lo que hace falta.
// TBD: para transferir, hay que tener un auth o llave, sino no te deja, tanto del usuario como del check transfer
// TBD: transfer total o parcial?, diff: se bloquea el cardnumberfrom
// DONDE determino si es puntos o bonus?????

	// SET ACTIONS

		// ADD or TRANSFER 
		// Aplicamos la transferencia
		if ($actionerrorid == 0) {
			

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');
				
				// TRANSFER AT TRANSACTIONS
				$items = 0;
				$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemBalanceTransferManage
									'".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."',
									'".$itemtype."', 'transfer', 'crm',
									'0', '".$cardnumberto."',
									'0', '".$cardnumberfrom."',
									'".$transfertype."', '".$transferauth."', '0',
									'1', '0', '0',
									'0';";
									//echo $query;
				$dbtransactions->query($query);
				$items = $dbtransactions->count_rows();
				if ($items > 0) {
					$my_row=$dbtransactions->get_row();
					$transferbalance = $my_row['CardBalance']; 
					$transferauth	 = $my_row['TransferAuth']; 
					$actionerrorid 	 = $my_row['Error']; 
				} else {
					$actionerrorid =  66; // if ($items > 0) { NOT FOUND
				}

		} 


		$TransferTypeDesc = "";
		if ($itemtype == "BONUS") 	{ $TransferTypeDesc = "Historial"; }
		if ($itemtype == "POINTS") 	{ $TransferTypeDesc = "Saldo"; }
		if ($itemtype == "RECORD") 	{ $TransferTypeDesc = "Actividades"; }
		if ($itemtype == "CARD") 	{ $TransferTypeDesc = "Tarjeta"; }
		if ($itemtype == "FULL") 	{ $TransferTypeDesc = "Tarjeta"; }
		if ($itemtype == "CARDRELEASE") 	{ $TransferTypeDesc = "Tarjeta"; }


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
                
						<?php 
                        // Si el usuario fue eliminado con exito....
                        if ($actionerrorid == 0) { 
                        ?>

                              <tr>
                                <td>
            
                                    <img src="images/iconresultok.png" /><br /><br />
                                    La TRANSFERENCIA ha sido APLICADA!.<br />
                                    <br />
                                    <br />
                                    El <span style="font-weight:bold;font-size:14px;"><?php echo $TransferTypeDesc; ?></span> de 
									<span style="font-weight:bold;font-size:18px;"><?php echo $cardnumberfrom; ?></span>
                                     ha sido transferido a 
									 <span style="font-weight:bold;font-size:18px;"><?php echo $cardnumberto; ?></span>.<br />
                                    <br />
            
                                </td>
                              </tr>                          
                              
                        <?php } else { ?>	
                              
                              <tr>
                                <td>
                                
									<?php 
                                    
                                    switch ($actionerrorid) {
                                        case 201:
                                            ?>    
                                                <img src="images/iconresultwrong.png" align="Error" /><br /><br />
                                                La TRANSFERENCIA ha sido RECHAZADA!.<br />
                                                <br />
                                                La transferencia ya fue aplicada con anterioridad.
                                                <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                            <?php  
                                            break;
                                        default:
                                            ?>    
                                                <img src="images/iconresultwrong.png" /><br /><br />
                                                La TRANSFERENCIA ha sido RECHAZADA!.<br />
                                                <br />
                                                Por favor, intente m&aacute;s tarde.&nbsp;
                                                <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                            <?php  
                                            break;
                                    }
    
                                    ?>
    
                                </td>
                              </tr>     
                        <?php } ?>	
                        
                        </table>
                        
                            <br /><br />
                            <table class="botones2">
                              <tr>
                                <td class="botonstandard">
                            	<img src="images/bulletaffiliated.png" />&nbsp;
                                <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumberfrom; ?>">Ver Tarjeta Origen</a>
                                </td>
                                <td class="botonstandard">
                            	<img src="images/bulletaffiliated.png" />&nbsp;
                                <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumberto; ?>">Ver Tarjeta Destino</a>
                                </td>
                                <td class="botonstandard">
                                <img src="images/bulletjoin.png" />&nbsp;
                                <a href="?m=affiliation&s=balancetransfer&a=new&t=<?php echo $itemtype; ?>&n=<?php echo $itemid; ?>">Nueva Transferencia <?php echo $TransferTypeDesc; ?></a>
                                </td>
                              </tr>
                            </table>
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

