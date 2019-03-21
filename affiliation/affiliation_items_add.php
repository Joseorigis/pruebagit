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

	// Inicializamos el marcador de error en la acción...
		$actionerrorid = 0;
		
	// Si falta algún parámetro (isset) enviados... ERROR
		if (!isset($_GET['cardnumber'])) {

			// Datos Invalidos
			$actionerrorid = 111;
			
		} 

		// Si no hay error hasta aquí, agregamos...
		if ($actionerrorid == 0) {
		
				// Varios en CHECKBOX
					$itemid = 0;
					$actionerrorid = 0;
					$cardnumbertyped = 0;
					$permission = 0;
					if (isset($_GET['permission'])) { $permission = 1; }
					$cardnumber = trim($_GET['cardnumber']);
					$affiliationauth = $_GET['affiliationauth'];
					// Generamos el nuevo password y lo encriptamos...
					$password = createRandomString(8);
					// NO hay tarjeta, por ende usamos el AffiliationAuth
					$password = $affiliationauth;
					
					// Agregamos el USER a la aplicación...
					$query  = " SET ANSI_NULLS ON;SET ANSI_WARNINGS ON;";
					$query .= " EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_AffiliationItemManage
											'add', 
											'crm',
											'".$_SESSION[$configuration['appkey']]['userid']."',
											'".$configuration['appkey']."',
											'0',
											'".$cardnumber."',
											'".md5($password)."',
											'".strtoupper(trim($_GET['name']))."',
											'".strtoupper(trim($_GET['lastname']))."',
											'',
											'".$_GET['year'].$_GET['month'].$_GET['day']."',
											'".$_GET['gender']."',
											'9999',
											'9999',
											'".$permission."',
											'',
											'".strtolower(trim($_GET['email']))."',
											'".$_GET['phone']."',
											'".$_GET['cellphone']."',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'',
											'MEXICO',
											'0',
											'0',
											'".$_GET['doctorname']."',
											'';";	
											
					//echo $query."<br>";
					$dbconnection->query($query);
					$records = $dbconnection->count_rows(); 
					$my_row=$dbconnection->get_row();
					
					$affiliationid	 	= $my_row['CardAffiliationId']; 
					$affiliationcard 	= $my_row['CardNumber']; 
					$affiliationname 	= $my_row['CardName']; 
					$affiliationstatus	= $my_row['CardStatus'];
					$affiliationstatusid= $my_row['CardStatusId'];
					$actionerrorid 		= $my_row['Error']; 
					//$actionerrorid 		= 0; 
					
						
		} // if ($actionerrorid == 0)
		
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
                
					<?php 
					// Imagen en el output
					$affiliatedimage = "images/imageuser.gif";
					if ($affiliationstatusid == 1) { $affiliatedimage = "images/imageuseractive.gif"; }
					if ($affiliationstatusid == 3) { $affiliatedimage = "images/imageuserwarning.gif"; }
					if ($affiliationstatusid == 4) { $affiliatedimage = "images/imageuserinactive.gif"; }
					if ($affiliationstatusid == 6) { $affiliatedimage = "images/imageuserdeleted.gif"; }
					if ($affiliationstatusid  > 1) { $affiliatedicon  = "images/bulletblock.png"; }
					$affiliatedicon = $affiliatedimage;
					
                    // Si el usuario fue eliminado con exito....
                    if ($actionerrorid == 0) { 
                    ?>
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Affiliated Status" title="Affiliated Status" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">AFILIACI&Oacute;N<br /><?php echo $affiliationstatus; ?></span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Tarjeta<br />
                            <span class="textMedium"><em><?php echo $affiliationcard; ?></em></span><br />
                            <br />
                            Afiliado<br />
                            <span class="textMedium"><em><?php echo $affiliationname; ?></em></span><br />
                            <br />
                            </td>
                          </tr>
                          <tr>
                            <td>
        
								<img src="images/iconresultok.png" /><br /><br />
                                La tarjeta ha sido AFILIADA!.<br />
                                <br />
        
                            </td>
                          </tr>                          
					<?php } else { ?>	
                          
                          <tr>
                            <td valign="bottom">
                            
                                    <table border="0">
                                      <tr>
                                        <td>
                       					<img src="<?php echo $affiliatedimage; ?>" class="imagenaffiliationuser" alt="Affiliated Status" title="Affiliated Status" />
                                        </td>
                                        <td width="24">&nbsp;</td>
                                        <td valign="bottom">
                                        <span class="textMedium">AFILIACI&Oacute;N<br /><?php echo $affiliationstatus; ?></span><br />
                                        </td>
                                      </tr>
                                    </table>
                            
                            </td>
                          </tr>
                          <tr>
                            <td>
                            Tarjeta<br />
                            <span class="textMedium"><em><?php echo $affiliationcard; ?></em></span><br />
                            <br />
                            Afiliado<br />
                            <span class="textMedium"><em><?php echo $affiliationname; ?></em></span><br />
                            </td>
                          </tr>
                          <tr>
                            <td>
                            
                            	<?php if ($actionerrorid == 202) { ?>
                                    <img src="images/iconresultwrong.png" />
<br /><br />
                                    La tarjeta NO pudo ser AFILIADA!.<br />
                                    <br />
                                    La tarjeta ya ha sido afiliada anteriormente.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } else { ?>    
                                    <img src="images/iconresultwrong.png" />
<br /><br />
                                    La tarjeta NO pudo ser AFILIADA!.<br />
                                    <br />
                                    Por favor, intente m&aacute;s tarde.&nbsp;
                                    <em>[Err <?php echo $actionerrorid; ?>]</em><br />
                                <?php } ?>    

                            </td>
                          </tr>     
					<?php } ?>	
                    </table>
                    
                    <?php $affiliatedicon = str_replace('2', '', $affiliatedicon); ?>

                        <br /><br />
                        <table class="botones2">
                          <tr>
                           <?php if ($actionerrorid == 0) { ?>
                            <td class="botonstandard">
                            <img src="<?php echo $affiliatedicon; ?>" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=affiliation&s=items&a=view&n=<?php echo $affiliationid; ?>">Ver Afiliado</a>
                            </td>
                            <?php } ?>
                           <?php if ($affiliationid > 0 && $actionerrorid == 210) { ?>
                            <td class="botonstandard">
                            <img src="<?php echo $affiliatedicon; ?>" width="14" height="14" class="imagenaffiliationusericon" />&nbsp;
                            <a href="?m=affiliation&s=items&a=view&n=<?php echo $affiliationid; ?>">Ver Afiliado</a>
                            </td>
                            <?php } ?>
                            <td class="botonstandard">
                            <img src="images/bulletnew.png" />&nbsp;
                            <a href="?m=affiliation&s=items&a=new">Nueva Afiliaci&oacute;n</a>
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
