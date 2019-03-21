<?php 
/**
* template.php
* 	Administra el login del usuario.
*	Despliega el loginform y procesa el login
*
* @author Raul Gutierrez <raul.gutierrez@loyaltydrivers.com>
* @date 20110103
* @version 20110103
* @comments 
*
*/

// CONTAINER
	// Si la invocación no viene del index, PAGE NOT FOUND
	if (!isset($appcontainer)) {
		header("HTTP/1.0 404 Not Found"); 	
		exit();
	} 

// Obtengo el nombre del script en ejecución
	$script = __FILE__;
	$camino = get_included_files();
	$scriptactual = $camino[count($camino)-1];

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

					// Obtengo el índice del paginado
						$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesIndicators
										'".$_SESSION[$configuration['appkey']]['userid']."','".$configuration['appkey']."',
										'points18', '', '18';";
						$dbconnection->query($query);
						$my_row=$dbconnection->get_row();
						$Indicator1	    	= $my_row['Indicator1'];
						$Indicator2	    	= $my_row['Indicator2'];
						$Indicator3	    	= $my_row['Indicator3'];
					
						$timeago = $my_row['TimeAgo'];
						$corte 	 = $my_row['TimeCurrent'];
						
					?>        

                        <br />
                        <table class="modulesectiontitle">
                            <tr>
                            <td>Reglas Puntos Acumulaci&oacute;n</td>
                            </tr>
                        </table>
                        <br />
                        <table class="tableresume">
                          <tr>
                            <td>
                            Reglas<br />
                            <span class="textLarge"><?php echo $Indicator1; ?></span><br />
                            <span class="textLight">[Total de reglas activas]</span>
                            </td>
                            <td>
                            Equivalencia Promedio<br />
                            <span class="textLarge"><?php echo $Indicator2; ?>%</span><br />
                            <span class="textLight">[Factor o Equivalencia promedio de acumulaci&oacute;n]</span>
                            </td>
                            <td>
                            Equivalencia Default<br />
                            <span class="textLarge"><?php echo $Indicator3; ?>%</span><br />
                            <span class="textLight">[Factor o Equivalencia Default de acumulaci&oacute;n]</span>
                            </td>
                          </tr>
                       </table>
						<br />
                        <div align="center">
						* Informaci&oacute;n al <?php echo $corte; ?>&nbsp;[<em><abbr class="timeago" title="<?php echo $timeago; ?>"><?php echo $timeago; ?></abbr></em>]
                        </div><br />

                
                    <br />
                    
                    
					<?php
			
					$query  = "EXEC ".$_SESSION[$configuration['appkey']]['appprefix']."dbo.usp_app_RulesPointsManage
										'".$_SESSION[$configuration['appkey']]['userid']."', 
										'".$configuration['appkey']."',
										'list', 
										'all', 
										'0',
										'',
										'18';";
					$dbconnection->query($query);
						
					?>        
			<!-- LIST GRID:begin -->    
                <table class="tablelist">
                  <thead>
                  <tr>
                    <td width="20%">Regla</td>
                    <td width="40%">Contenido</td>
                    <td width="10%">Factor</td>
                    <td width="15%">Status</td>
                    <td width="15%">Fecha</td>
                  </tr>
                  </thead>
                  <tbody>
                  
                  	<?php
					while($my_row=$dbconnection->get_row()){ 
					?>				  
                      <tr>
                        <td>
                        <a href="?m=rules&s=points&a=view&n=<?php echo $my_row['RulePointsEquivalenceId']; ?>">
						<?php echo $my_row['RuleName']; ?>
                        </a>
                        </td> 
                        <td><?php echo $my_row['RuleDescription']; ?></td>
                        <td><?php echo $my_row['RuleEquivalence']; ?>%</td>
                        <td><?php echo $my_row['RulePublishStatus']; ?></td>
                        <td><?php echo $my_row['RuleCreatedDate']; ?></td>
                      </tr>

                  	<?php
					}
					?>				  

                  </tbody>
                </table>
                
                    <table width="100%">
                      <tr>
 						<td align="center">
                            <table >
                              <tr>
                                <td class="botonstandard"><img src="images/bulletnew.png" />&nbsp;<a href="?m=rules&s=points&a=new">Nueva Regla</a></td>
                              </tr>
                            </table>
                        </td>
                      </tr>
                    </table>
                
			<!-- LIST GRID:end -->                         

                    <br />
                    <br />
                
                </td>
		    <!-- MODULO BODY: end -->


            <!-- MODULO TOOLBAR: begin -->
        <td class="templatesidebar">

					<!-- Incluimos el sidebar del modulo-->
                    <?php 

					// Armamos dinamicamente el nombre del sidebar
					$sidebarfile = str_replace(".php", "_sidebar.php", $modulepage);
					
					// Verificamos si existe el archivo
					if (file_exists($sidebarfile)) { 
						
						// Incluimos la barra lateral
						include_once($sidebarfile); 
						
					} else { 
					
						// Si no hay barra, activamos la default
						$sidebarfile = "home_sidebar.php";
						include_once($sidebarfile); 
						
					} 	
					
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
