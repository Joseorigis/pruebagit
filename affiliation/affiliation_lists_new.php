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
//		if ($requestsource !== 'domain' && $requestsource !== 'page') {
//			$actionerrorid = 10;
//			include_once("accessdenied.php"); 
//			exit();
//		}


	// PARAMETER VALIDATION
		// Obtenemos el ID del item
			$itemid = 0;
			if (isset($_GET['n'])) {
				$itemid = setOnlyNumbers($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
				if (!is_numeric($itemid)) { $itemid = "0"; }
			}
		// Obtenemos el itemtype, el tipo de elemento a consultaar
			$itemtype = 'list';
			if (isset($_GET['t'])) {
				$itemtype = setOnlyLetters($_GET['t']);
				if ($itemtype == '') { $itemtype = 'list'; }
			}
			$itemtype = strtoupper($itemtype);

//		// actionauth 
//			$actionauth = '';
//			if (isset($_GET['actionauth'])) { $actionauth = setOnlyText($_GET['actionauth']); } 
//			if  (isValidActionAuth($actionauth) == 0) { $actionerrorid = 2; } // Obligatorio
//			if  ($actionauth == '') { $actionerrorid = 2; } // Obligatorio

		// LISTID
			$listid = 0;
			if (isset($_GET['n'])) {
				$listid = setOnlyNumbers($_GET['n']);
				if ($listid == "") { $listid = 0; }
				if (!is_numeric($listid)) { $listid = "0"; }
			}

		// LISTNAME
			$listname = "";
			if (isset($_GET['listname'])) {
				$listname = setOnlyCharactersValid($_GET['listname']);
			}

		// LIST TYPE
			$listtype = 'dynamic';
			if (isset($_GET['listtype'])) {
				$listtype = setOnlyLetters($_GET['listtype']);
				if ($listtype == '') { $listtype = 'dynamic'; }
			}
			$listtype = strtolower($listtype);

		// LIST CONTENT
			$listcontent = '';
			if (isset($_GET['listcontent'])) {
				$listcontent = setOnlyCharactersValid($_GET['listcontent']);
			}

		// LIST ENCODED
			$listencoded = 0;
			if (isset($_GET['listencoded'])) { $listencoded = 1; }


	// RECORD PROCESS...	
			// if there is record...
			if ($itemid !== "0") {
					// Get Record...
					$records = 0;
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationListsManage 
											'".$_SESSION[$configuration['appkey']]['userid']."', 
											'".$configuration['appkey']."',
											'view', 
											'".$listtype."', 
											'".$itemid."';";//echo $query;
					$dbconnection->query($query);
					$records = $dbconnection->count_rows();
					if ($records > 0) {
						$my_row=$dbconnection->get_row();

						$listname 		= $my_row['ListName'];
							if (isset($my_row['ListSQLQueryDecoded'])) {
								$listcontent = $my_row['ListSQLQueryDecoded'];
							}
							if (isset($my_row['ListType'])) {
								$listtype = strtolower($my_row['ListType']);
							}
							if (isset($my_row['ListEncoded'])) {
								$listencoded = $my_row['ListEncoded'];
							}
						//$actionerrorid 	= $my_row['Error']; 
						$actionerrorid 	= 0;

					} else {
						$actionerrorid = 66;
					}

			} // [if ($itemid !== "0") {]
?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		var listcontent = new String();
	
		if(WithoutContent(document.orveefrmaffiliated.listname.value))
			{ errormessage += "\n- Ingresa el nombre para la lista."; }
	
		if(WithoutContent(document.orveefrmaffiliated.listcontent.value))
			{ errormessage += "\n- Ingresa el SQL para la lista."; }
	
		//if(document.orveefrmaffiliated.n.value == "0")
		//	{ errormessage += "\n- No hay afiliado seleccionado, no podrás agendar la actividad!."; }

		//if(document.orveefrmaffiliated.n.value == "")
		//	{ errormessage += "\n- No hay afiliado seleccionado, no podrás agendar la actividad!."; }


		// Put field checks above this point.
		if(errormessage.length > 2) {
			//var contenidoheader = "<p class='messagealert'><strong>Oooops!</strong><br />Por favor...<br />";
			//var contenidofooter = "</p>";
			alert('Para agregar la lista, por favor: ' + errormessage);
			//document.getElementById("loginresult").innerHTML = contenidoheader+errormessage+contenidofooter;
			//document.getElementById("botonsubmit").innerHTML = "<img src='images/imageloading.gif' />&nbsp;&nbsp;&nbsp;<em>Afiliación en proceso, por favor, espere un momento...</em>";
			
			return false;
			}

		// Encode before we send
		//document.orveefrmaffiliated.listcontent.value = encodeURI(document.orveefrmaffiliated.listcontent.value);
		listcontent = document.orveefrmaffiliated.listcontent.value;
		//document.orveefrmaffiliated.listcontent.value = listcontent.replace("'","°");
		document.orveefrmaffiliated.listcontent.value = listcontent.replace(/'/g, "°");
	
		return true;
		//document.orveefrmuser.submit();
		//return true;
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
                <input name="s" type="hidden" value="lists" />
                <input name="a" type="hidden" value="add" />
                <input name="n" id="n" type="hidden" value="<?php echo $itemid; ?>" />
                <input name="actionauth" id="actionauth" type="hidden" value="<?php echo $actionauth; ?>" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imageaffiliationlists.png" alt="Affiliated Status" title="Affiliated Status" class="imagenaffiliationuser" />
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">
                                Nueva Lista
                                </span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                     Nombre<br/>
                    <input name="listname" id="listname" type="text" class="inputtextrequired" size="50" value="<?php echo $listname; ?>" /><br />
                    <span class="textHint">
                    &middot; Nombre para la nueva lista.
                    </span></td>
                  </tr>
                  <tr>
                    <td>
                    Tipo<br />
                    <div class="fieldrequired">
                    <input name="listtype" type="radio" value="dynamic" <?php if($listtype == "dynamic") { echo 'checked="checked"'; } ?> />&nbsp;DINAMICA [La lista se regenera cada ocasión que es usada.]<br />
					<input name="listtype" type="radio" value="static"  <?php if($listtype == "static") { echo 'checked="checked"'; } ?> />&nbsp;ESTATICA [La lista se genera una sola vez, con una copia de los elementos en este momento.]<br />
					</div>
                        <span class="textHint">
                      &middot; Tipo de lista a crear.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Contenido<br />
                    <textarea name="listcontent" id="listcontent" class="textrequired" cols="100" rows="10" maxlength="2000"><?php echo $listcontent; ?></textarea><br />
                    <span class="textHint"> &middot; SQL para la lista.</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                    Consideraciones Especiales<br />
                    <input name="listencoded"  id="listencoded" type="checkbox" <?php if ($listencoded == "1") { echo "checked='checked'"; } ?> /> La Lista ser&aacute; usada en Interacciones de EMAIL o SMS<br />
                    <span class="textHint"><span style="color:#ff0000;">&middot; Encriptar lista para env&iacute;o de Email o SMS.</span></span>
                    </td>
                  </tr>
							<?php if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
									$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>                               
                  
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Guardar" />
                    </div>
                    </td>
                  </tr>
							<?php } ?>                                                   
                  
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

