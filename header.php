<?php

	// INCLUDES & REQUIRES 
		include_once('includes/configuration.php');	// Archivo de configuración
		include_once('includes/functions.php');	// Librería de funciones
		include_once('includes/database.class.php');	// Class para el manejo de base de datos
		include_once('includes/databaseconnection.php');	// Conexión a base de datos

?>
							<?php
								$switchtostyles = "";
								$switchtocontent = "";
								$switchindex = 0;
							
                                // Obtengo el contenido del switch
                                $query  = "EXEC dbo.usp_app_UtilityCategoryElements
														'UserSwitch', 
														'".$configuration['appkey']."';";
                                $dbsecurity->query($query);
								while($my_row=$dbsecurity->get_row()){ 
								
										$switchindex = $switchindex + 1;
									//$my_row['TransactionType']
										$switchtostyles .= "$('#crm_link".$switchindex."').tipsy({gravity: 'w'});".PHP_EOL;
										
										$switchtocontent .= "<img src='images/toggle_right_dark.png' alt='DrowDownArrow' />&nbsp;";
                                        $switchtocontent .= "<a href='switch.php?q=".urlencode(base64_encode($my_row['ItemKey']))."' id='crm_link".$switchindex."' title='Iniciar Sesi&oacute;n en ".$my_row['Item']."' target='_BLANK'>".$my_row['Item']."</a><br />".PHP_EOL;

										
								}
								
								if ($switchtocontent == "") { $switchtocontent = "&nbsp;"; }
                            
                            ?>


                <!-- DROPDOWN SCRIPTS -->
                    <script type="text/javascript">
                            $(document).ready(function() {
                    
                                $(".oswitch").click(function(e) {          
                                    e.preventDefault();
                                    $("div#dropdowncontent").toggle();
                                    $(".oswitch").toggleClass("menu-open");
                                    //$(".oswitch").html("<span><img src='bulletswitch1off.png' alt='Switch' /></span>");
                                });
                                
                                $("div#dropdowncontent").mouseup(function() {
                                    return false
                                });
                                $(document).mouseup(function(e) {
                                    if($(e.target).parent("a.oswitch").length==0) {
                                        $(".oswitch").removeClass("menu-open");
                                    	//$(".oswitch").html("<span><img src='bulletswitch1.png' alt='Switch' /></span>");
                                        $("div#dropdowncontent").hide();
                                    }
                                });			
                                
                            });
                    </script>
                    <script type='text/javascript'>
                        $(function() {
                          $('#myaccount_link').tipsy({gravity: 'w'});   
                          $('#password_link').tipsy({gravity: 'w'});   
                          $('#logout_link').tipsy({gravity: 'w'});   
						  
						  <?php echo $switchtostyles; ?>
						  
                        });
                      </script>
            
                    <div id="dropdowncontainer">

						<?php
                            // Imagen en el output
                            $icono = "images/iconuser.gif";
                            if ($_SESSION[$configuration['appkey']]['userstatusid'] == 1) { $icono = "images/iconuseractive.gif"; }
                            if ($_SESSION[$configuration['appkey']]['userstatusid'] == 3) { $icono = "images/iconuserwarning.gif"; }
                            if ($_SESSION[$configuration['appkey']]['userstatusid'] == 6) { $icono = "images/iconuserinactive.gif"; }
                            
                            if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1) { $icono = "images/iconuseradmin.gif"; }
                            if ($_SESSION[$configuration['appkey']]['userprofileid'] == 2) { $icono = "images/iconuseradmin.gif"; }	
                        ?>
                    
                        <div id="dropdownnav" class="dropdownnav">
                        <!--<strong><?php echo $_SESSION[$configuration['appkey']]['username']; ?></strong>@<?php echo $configuration['appkey']; ?>-->
                        &nbsp;&nbsp;
                        <a href="?m=home" title="Ir a Inicio"><img src="images/bulletheaderhome.png" alt="Inicio" /></a>
                        &nbsp;
                        <a href="?m=myaccount" title="Ir a Mi Cuenta"><img src="images/bulletheadermyaccount.png" alt="Mi Cuenta" /></a>
                        &nbsp;
                        <a href="?m=logout" title="Salir"><img src="images/bulletheaderlogout.png" alt="Salir" /></a>
                        <!--<a href="#" class="lightbox_trigger">Test</a>-->
                        <a href="#" class="oswitch"><span><img src="images/spacer.gif" alt="Switch" /></span></a>
                        </div>
                        
                        <div id="dropdowncontent">
                            <table cellpadding="3" width="100%">
                              <tr>
                                <td width="50%" valign="bottom">
                                <img src="<?php echo $icono; ?>" alt="User Status" title="User Status" class="oswitchimagenuser" />
                                <br />
                                <span class="oswitchuser"><?php echo $_SESSION[$configuration['appkey']]['username']; ?></span>
                                <br />
                                <img src="images/toggle_right_dark.png" alt="DrowDownArrow" />&nbsp;
                                <a href="?m=myaccount" id="myaccount_link" title="Ir a Mi Cuenta">Mi Cuenta</a>
                                <br />
								<?php if ($_SESSION[$configuration['appkey']]['userldap'] == 0) { ?>                                
                                <img src="images/toggle_right_dark.png" alt="DrowDownArrow" />&nbsp;
                                <a href="?m=security&s=users&a=passwordchange" id="password_link" title="Ir a Cambiar Contrase&ntilde;a">Cambiar Mi Contrase&ntilde;a</a>
                                <br />
                                <?php } ?>
                                <img src="images/toggle_right_dark.png" alt="DrowDownArrow" />&nbsp;
                                <a href="?m=logout" id="logout_link" title="Salir de Orvee CRM">Salir</a>
                                </td>
                                
                                <!-- HEADER SWITCH: begin -->
                                <?php 
								if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1 ||
									$_SESSION[$configuration['appkey']]['userprofileid'] == 2) { ?>
                                <td width="50%" valign="bottom">

									  <?php echo $switchtocontent; ?>
                                
                                </td>
                                <?php } ?>
                                <!-- HEADER SWITCH: end -->
                                
                              </tr>
                            </table>
                        </div>
                        
                    </div>
