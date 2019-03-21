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

		$actionauth = '';
		if (isset($_GET['actionauth'])) {
			$actionauth = setOnlyText($_GET['actionauth']);
		}

		$transferauth = '';
		if (isset($_GET['transferauth'])) {
			$transferauth = setOnlyText($_GET['transferauth']);
		}

		$points = '0';
		if (isset($_GET['points'])) {
			$points = setOnlyNumbers($_GET['points']);
			if (!is_numeric($points)) { $actionerrorid = 2; }
		}
		$pointsreference = '';
		if (isset($_GET['pointsreference'])) {
			$pointsreference = setOnlyText($_GET['pointsreference']);
		}


	// SET ACTIONS
		$affiliationid		= $itemid;
		//$cardnumber			= "0";
		$affiliationcard 	= "0"; 
		$affiliationname 	= ""; 

		// ADD
		// Aplicamos la transferencia
		if ($actionerrorid == 0) {
			

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
				
				$items = 0;
				$query = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemBalanceTransferManage
									'".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."',
									'".$itemtype."', 'add', 'crm',
									'0', '".$cardnumber."',
									'0', '',
									'complete', '".$actionauth."', '".$points."',
									'1', '0', '0',
									'".$pointsreference."';";
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
                                    <?php echo $affiliationcard; ?><br />
                                    <span class="textSmall"><?php echo $affiliationname; ?><br /></span>
                                    Nueva Transferencia PUNTOS
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
            
                                    <img src="images/iconresultok.png" /><br /><br />
                                    La TRANSFERENCIA ha sido APLICADA!.<br />
                                    <br />
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
                                <a href="?m=affiliation&s=items&a=view&q=<?php echo $cardnumber; ?>">Ver Afiliado</a>
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

