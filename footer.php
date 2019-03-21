<!-- FOOTER: begin -->
    <table class="headerfooter">
        <?php if (isset($_SESSION[$configuration['appkey']])) { ?>
              <tr>
                <td align="center">
                <a href="?m=affiliation">Afiliaci&oacute;n</a> | 
                <a href="?m=interactions">Interacciones</a> | 
                <a href="?m=rules">Reglas Negocio</a> | 
                <a href="?m=rewards">Recompensas</a> | 
                <a href="?m=reports">Reportes</a> | 
                <a href="?m=security">Seguridad</a>
                </td>
              </tr>
              <tr>
                <td align="center">
                <a href="?m=home" title="Inicio">Inicio</a> | 
                <a href="?m=myaccount" title="Mi Cuenta">Mi Cuenta</a> | 
                <a href="?m=logout" title="Salir">Salir</a>
                </td>
              </tr>       
        <?php } ?>
      <tr>
        <td align="center"><a href="?m=terms">T&eacute;rminos de Uso</a> | <a href="?m=privacy">Pol&iacute;tica de Privacidad</a></td>
      </tr>
      <tr>
        <td align="center"><?php echo $configuration['appcopyright']; ?></td>
      </tr>
      <tr>
        <td align="center" height="48">
        <img src="images/FooterLogoOrigis2.png" alt="Copyright" />
        <!--<img src="images/spacer.gif" alt="Spaces" width="40" />
        <img src="images/FooterLogoOrigisLoyalty2.png" alt="Copyright" />-->
        </td>
      </tr>
      <tr>
        <td align="center" class="textInvisible"><br /><?php echo session_id()." - ".date('Ymd His'); ?></td>
      </tr>
    </table>
<!-- FOOTER: end -->
