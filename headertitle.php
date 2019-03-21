    <!-- MODULO HEADER:begin -->
    <table width="100%" border="0" cellspacing="0">
      <tr>
        <td width="80%" align="left">
            <!-- NAVIGATION PATH: begin -->
            <span class="templatepath">
                <?php echo getPageNavigationPath($module,$section,$action,''); ?>
            </span><br />
            <!-- NAVIGATION PATH: end -->
            <br />&nbsp;
            <span class="templatetitle">
                <?php echo getPageTitle($module,$section,$action,''); ?>
            </span>
        
        </td>
        <td width="20%" align="left" style="padding: 0px 0px 0px 10px;">
        
				<!-- USER CURRENT:begin -->
                <?php
					// Imagen en el output
					$icono = "images/iconuser.gif";
					if ($_SESSION[$configuration['appkey']]['userstatusid'] == 1) { $icono = "images/iconuseractive.gif"; }
					if ($_SESSION[$configuration['appkey']]['userstatusid'] == 3) { $icono = "images/iconuserwarning.gif"; }
					if ($_SESSION[$configuration['appkey']]['userstatusid'] == 6) { $icono = "images/iconuserinactive.gif"; }
					
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 1) { $icono = "images/iconuseradmin.gif"; }
					if ($_SESSION[$configuration['appkey']]['userprofileid'] == 2) { $icono = "images/iconuseradmin.gif"; }	
			
				?>
                <table width="100%" border="0" cellspacing="1" cellpadding="2" align="left">
                    <tr>
                    <td align="left" width="36">
                    <img src="<?php echo $icono; ?>" alt="User Status" title="User Status" class="imagensecurityuser" />&nbsp;
                    </td>
                    <td style="font-size:9px;" align="left">
                    <a href="?m=myaccount" title="Ir a Mi Cuenta">
                    	<strong><?php echo $_SESSION[$configuration['appkey']]['username']; ?></strong>
                    </a><br />
                    <?php echo $_SESSION[$configuration['appkey']]['name']; ?><br />
					<?php echo $configuration['appkey']; ?>
                    </td>
                    </tr>
                </table>
				<!-- USER CURRENT:end -->        
        
        </td>
      </tr>
    </table>
    <!-- MODULO HEADER:end -->
    