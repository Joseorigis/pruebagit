<?php 
/**
*
* TYPE:
*	INDEX REFERENCE
*
* interactions.php
* 	Página principal del módulo de administración de interacciones con los afiliados.
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

	// REFERER
		// Identificamos de donde viene...
		$referer = "";
		if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
		$referer = str_replace($_SESSION[$configuration['appkey']]['appurl'],'',$referer);
		if ($referer == "") { $referer = "index.php"; }
	
	
	// ITEMID
			// Obtenemos el ID del Item
			$itemid = "";
			if (isset($_GET['n'])) {
				$itemid = setOnlyText($_GET['n']);
				if ($itemid == "") { $itemid = 0; }
			}


	// STORE
			// Obtenemos el ID del store
			$storeid = 0;
			if (isset($_GET['store'])) {
				$storeid = setOnlyText($_GET['store']);
				if ($storeid == "") { $storeid = 0; }
			}
?>

<SCRIPT type="text/javascript">
<!--

	function CheckRequiredFields() {
		var errormessage = new String();
		
		if(WithoutContent(document.orveefrmrule.n.value))
			{ errormessage += "\n- Ingresa un artículo a consultar!."; }
			
			
		// Put field checks above this point.
		if(errormessage.length > 2) {
			alert('Para consultar la equivalencia, por favor: ' + errormessage);
			
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

                <form action="index.php" method="get" name="orveefrmrule" onsubmit="return CheckRequiredFields();">
                <input name="m" type="hidden" value="rules" />
                <input name="s" type="hidden" value="pointsitem" />
                <input name="a" type="hidden" value="check" />
                <table border="0" cellspacing="0" cellpadding="10">
                  <tr>
                    <td valign="bottom">
                    
                            <table border="0">
                              <tr>
                                <td>
                                <img src="images/imagerules.png" alt="Reward Status" title="Reward Status" class="imagenaffiliationuser" />						
                                </td>
                                <td width="24">&nbsp;</td>
                                <td valign="bottom">
								<span class="textMedium">Consultar Equivalencia</span><br />
                                </td>
                              </tr>
                            </table>
                    
                    </td>
                  </tr>

                  <tr>
                    <td>
                    Art&iacute;culo<br/>
                    <input name="n" id="n" type="text" class="inputtextrequired" size="50" value="<?php echo $itemid; ?>"/><br />
                    <span class="textHint">
                    &middot; Art&iacute;culo o SKU a consultar.
                    </span></td>
                  </tr>
                  <tr>
                    <td>
                    Sucursal<br />
                    <select name="store" id="store" class="selectrequired">
	                    <option value="0">[Todas]</option>
						<?php
                            $query  = " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_UtilityCategoryElements 
														'ConnectionStores', '',
														'".$_SESSION[$configuration['appkey']]['userid']."', '".$configuration['appkey']."';";
                            $dbtransactions->query($query);
                            while($my_row=$dbtransactions->get_row()){ 
                                if ($my_row['ItemIsSelected'] == 1) {
                                    echo "<option value='".$my_row['ItemId']."' selected='selected'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                } else {
                                    echo "<option value='".$my_row['ItemId']."'>";
                                    echo "&nbsp;".$my_row['Item']."</option>";
                                }
                            }
                        ?>
                    </select><br />
                    <span class="textHint"> 
                    &middot; Sucursal o tienda a consultar el art&iacute;culo.<br />
                    </span>
                    </td>
                  </tr>
                  
                  <tr>
                    <td>
                    <div id="botonsubmit">
                    <input name="submitbutton" id="submitbutton" type="submit" value="Consultar" />
                    </div>
                    </td>
                  </tr>
                </table>
				</form>
                
                <br />
				<br />

                <?php if (isset($_GET['n'])) {
						$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
											'".$_SESSION[$configuration['appkey']]['userid']."', 
											'".$configuration['appkey']."',
											'view', 
											'item', 
											'0',
											'',
											'0',
											'".$storeid."',
											'',
											'',
											'',
											'".$itemid."';";
						$dbtransactions->query($query);
						
						$items = 0;
				?>

                            	<!-- LIST GRID:begin -->            
                                    <table class="tablelistitems">
                                      <thead>
                                      <tr>
                                        <td align="center">&nbsp;</td>
                                        <td>Art&iacute;culo</td>
                                        <td align="center">Equivalencia</td>
                                        <td>Sucursal</td>
                                        <td>Vigencia</td>
                                        <td>Regla</td>
                                        <td>Alta</td>
                                      </tr>
                                      </thead>
                                      <tbody>
                                
									<?php
                                    while($my_row=$dbtransactions->get_row()){ 
											$items = $items + 1;
									  ?>
										  <tr>
											<td align="center">
												<?php 
                                                if ($items == 1) {
                                                    echo "<img src='images/iconacceptblue.png' alt='Active Equivalence' title='Equivalencia Aplicada' />";
                                                } else {
                                                    echo $items; 
                                                }
                                                ?>
                                            </td>
											<td><?php echo $my_row['Item']; ?></td>
											<td align="center"><?php echo $my_row['Equivalence']; ?>%</td>
											<td><?php echo $my_row['EquivalenceStore']; ?></td>
											<td>
                                            	Del <?php echo $my_row['RuleActivationDate']; ?> 
                                                al <?php echo $my_row['RuleExpirationDate']; ?>
                                            </td>
											<td>
                                                <?php  if ($my_row['RulePointsEquivalenceId'] == '0') { ?>
                                                		<span style="font-style:italic;"><?php echo $my_row['RuleName']; ?></span>
                                                <?php  } else { ?>
														<a href="?m=rules&s=points&a=view&n=<?php echo $my_row['RulePointsEquivalenceId']; ?>" title="Ver Regla" target="_blank">
														<?php echo $my_row['RuleName']; ?>
														</a>
                                                <?php  } ?>
                                            
                                            </td>
											<td><?php echo $my_row['EquivalenceDate']; ?></td>
										  </tr>
                                          
									 <?php
									  }
									  if ($items == 0) {
										?>
										<tr>
										<td align="center" colspan="7">
										<div align="center"><em>Sin Equivalencia</em></div>
										</td>
										</tr>
										<?php
									  }
									  ?>
                                        <tr>
                                        <td class="itemdetailfootnote" colspan="7">
                                        * Las reglas NUEVAS se pueden consultar al d&iacute;a siguiente.
                                        </td>
                                        </tr>
									  </tbody>
									  </table>
                                      <br />
								<!-- LIST GRID:end -->   
                
                <?php } ?>

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

