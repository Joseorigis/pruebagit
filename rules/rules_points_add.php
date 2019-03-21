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

			// TRANSACTIONS DATABASE
				include_once('includes/databaseconnectiontransactions.php');

		$actionerrorid = 0;
		$itemid = 0;
		
		$rulename = "";
		if (isset($_GET['rulename'])) { $rulename = $_GET['rulename']; }
		$rulecode = "";
		if (isset($_GET['rulecode'])) { $rulecode = $_GET['rulecode']; }

		$items = "";
//		if (isset($_GET['itemtype0'])) { $items .= $_GET['itemtype0']."|"; }
//		if (isset($_GET['itemtype1'])) { $items .= $_GET['itemtype1']."|"; }
//		if (isset($_GET['itemtype2'])) { $items .= $_GET['itemtype2']."|"; }
//		if (isset($_GET['itemtype3'])) { $items .= $_GET['itemtype3']."|"; }
//		if (isset($_GET['itemtype4'])) { $items .= $_GET['itemtype4']."|"; }
		if (isset($_GET['itemtype1'])) { 
			if ($_GET['itemtype1'] == '') {		
				$items .= "0|";
			} else {
				$items .= $_GET['itemtype1']."|";
			}
		}
		if (isset($_GET['itemtype2'])) { 
			if ($_GET['itemtype2'] == '') {		
				$items .= "0|";
			} else {
				$items .= $_GET['itemtype2']."|";
			}
		}

		$ActivationDate = $_GET['ruleactivation'];
		$ActivationDate = str_replace("/","",$ActivationDate);
		$ActivationDate = substr($ActivationDate,4,4).substr($ActivationDate,2,2).substr($ActivationDate,0,2);

		$ExpirationDate = $_GET['ruleexpiration'];
		$ExpirationDate = str_replace("/","",$ExpirationDate);
		$ExpirationDate = substr($ExpirationDate,4,4).substr($ExpirationDate,2,2).substr($ExpirationDate,0,2);


		$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
							'".$_SESSION[$configuration['appkey']]['userid']."', 
							'".$configuration['appkey']."',
							'add', 
							'".$_GET['ruletype']."', 
							'".$_GET['actionauth']."',
							'".$rulename."',
							'18',
							'".$_GET['store']."',
							'".$_GET['equivalence']."',
							'".$ActivationDate."',
							'".$ExpirationDate."',
							'".$items."',
							'".$rulecode."';";
		$dbtransactions->query($query);
		//$items = $dbconnection->count_rows();
		$my_row=$dbtransactions->get_row();
		$itemid = $my_row['RulePointsEquivalenceId'];
		$actionerrorid = $my_row['Error'];

?>

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

     function getItemCount(urlscript, htmlelement) {
		 
		   var itemtype1 =  document.getElementById('itemtype1').value;
		   var itemtype2 =  document.getElementById('itemtype2').value;
		 
		   var element =  document.getElementById(htmlelement);
		   
		   if(urlscript.indexOf('?') != -1) {
			   var fragment_url = urlscript+'&ItemType1='+itemtype1+'&ItemType2='+itemtype1;
		   }else{
			   var fragment_url = urlscript+'?ItemType1='+itemtype1+'&ItemType2='+itemtype1;
		   }
		   
		   element.innerHTML = '<i>Consultando...</i>';
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

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="#">
                <input name="itemtype0" id="itemtype0" type="hidden" value="<?php echo $_GET['itemtype0']; ?>" />
                <input name="itemtype1" id="itemtype1" type="hidden" value="<?php echo $_GET['itemtype1']; ?>" />
                </form>

                <table border="0" cellspacing="0" cellpadding="10">
                
					<?php 
					
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">
                                        Regla<br />
										Nueva Regla Puntos
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo $my_row['RuleName']; ?></em></span><br />
                            <br />
                            La regla de acumulaci&oacute;n de 
                            <strong><?php echo $my_row['PointsEquivalence']; ?>%</strong> de equivalencia en puntos para <br />
                            <strong><?php echo $my_row['RuleDescription']; ?></strong> con <br />
							<div id="DivItemsCount">0</div> art&iacute;culos<br />
							con vigencia del <strong><?php echo $my_row['PointsActivationDate']; ?> al <?php echo $my_row['PointsExpirationDate']; ?></strong>, ha sido cargada para publicarse el <strong><?php echo $my_row['RulePublishDate']; ?></strong>.<br />
							<br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La regla ha sido CARGADA!.<br />
                                <br />
        
                            </td>
                          </tr>         
                                           
					<?php } else { ?>	
                          
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">
                                        Regla<br />
										Nueva Regla Puntos
                                        </span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Regla<br />
                            <span class="textMedium"><em><?php echo $rulename; ?></em></span><br />
                            <br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            
                            	<?php 
								
								switch ($actionerrorid) {
									case 14:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser CARGADA!.<br />
                                            <br />
                                            La regla ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									case 102:
										?>    
                                            <img src="images/iconresultwrong.png" /><br /><br />
                                            La regla NO pudo ser CARGADA!.<br />
                                            <br />
                                            La regla o su configuraci&oacute;n ya ha sido cargada anteriormente.&nbsp;
                                            <em>[Err <?php echo $actionerrorid; ?>]</em><br />
										<?php  
										break;
									default:
										?>    
											<img src="images/iconresultwrong.png" /><br /><br />
											La regla NO pudo ser CARGADA!.<br />
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

			<script language="javascript" type="text/javascript">
                getItemCount('rules/rules_points_itemscount.php', 'DivItemsCount'); 
            </script>
                    
                        <br /><br />
                        <table class="botones2">
                          <tr>
                           <?php if ($actionerrorid == 0) { ?>
                            <td class="botonstandard">
                            <img src="images/imagerules.png" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=rules&s=points&a=view&n=<?php echo $itemid; ?>">Ver Regla</a>
                            </td>
                            <?php } ?>
                           <?php if ($actionerrorid == 102) { ?>
                            <td class="botonstandard">
                            <img src="images/imagerules.png" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=rules&s=points&a=view&n=<?php echo $itemid; ?>">Ver Regla</a>
                            </td>
                            <?php } ?>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=rules&s=points&a=new">Nueva Regla</a>
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

