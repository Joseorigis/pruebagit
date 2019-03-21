<?php
   require("../../configuracion.php");
   $idSysSection = $sectionCampEmail;
   $path = "../../";
   require("../../application-top.php");
   
   $idSection = $_GET['id'];
   $baseURL = $appinstanceurl;

   $href       = "<a href='".$baseURL."tracking/trackEmailClick.php?idSection=".$idSection."&idUser=|USERID|&idEnvio=|ENVIOID|'>click</a>";
   $img        = "<IMG src='".$baseURL."tracking/trackEmailView.php?idUser=|USERID|&idEnvio=|ENVIOID|' alt='|EMAILID|' border='0' width='1' height='1' />";
   $noview     = "<a href='".$baseURL."tracking/trackEmailNoView.php?idSection=0&idUser=|USERID|&idEnvio=|ENVIOID|'>click</a>";
   $unsuscribe = "<a href='".$baseURL."tracking/trackEmailUnsuscribe.php?idSection=6&idUser=|USERID|&email=|EMAIL|&idEnvio=|ENVIOID|'>UNSUSCRIBE</a>";
   
?>
<html>
<head>
  <title><?= $instanciaECRM ?></title>
  <link rel="stylesheet" href="../../styles/style.css" type="text/css">
</head>
<body leftmargin="0" class="mainBackground" topmargin="0" marginwidth="0" marginheight="0">
<table width="100" border="0" cellpadding="0" cellspacing="0">
  <tr><td><img src="../../images/headerBar.gif" width="590" height="60" alt=""></td></tr>
  <tr>
    <td><table width="800" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td bgcolor="#FFFFFF" valign="top" height="400">

<table border="0" cellpadding="5" cellspacing="0" width="750">
  <tr><td colspan="2"><table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr><td colspan="2"><img src="images/spacer.gif" height="10" width="1" alt=""></td></tr><tr><td><strong class="pageTitle">Campa&ntilde;as E-mail</strong><br><strong class="pageSubtitle">Administrador Secciones y Tracking</strong></td><td align="right"><img src="../../images/trademark.gif" alt="" border="0"><br>
<strong class="systemTitle"><?= $instanciaECRM ?></strong><br>Perfil <strong><?= $PerfilUsuario ?></strong></td></tr>
  </table></td></tr>
  <tr><td>
    <table border="0" cellpadding="5" cellspacing="1" class="mainTableBG">
      <tr class="answerCellBG">
        <td><img src="../../images/icArrowGo.gif" height="13" width="13" alt=""></td>
        <td><a href="email_admin.php" target="content">Regresar</a></td>
          </tr>
        </table>
      </td></tr>
      <tr><td colspan="2"><img src="../images/spacer.gif" height="10" width="1" alt=""></td></tr>
      <tr>
	  <td>
	  
	  
	  <br>
	  <strong class="pageSubtitle">Secciones en Emailing y Tracking</strong><br>
      <font color="#999999">Ejemplo de Secci&oacute;n.</font> <br>
      <br>
      <table border='0' cellpadding='5' cellspacing='1' class='secTableBG' width='100%'>
        <tr class="mainTableBG">
          <td bgcolor='#0000FF'><strong class="secTableTitle">Tracking CLICK &nbsp; [EJEMPLO SELECCIONADO]</td>
        </tr>
        <tr>
          <td class="answerCellBG" valign="top"> UBICACI&Oacute;N: <i>Se coloca como en vez del v&iacute;nculo a la p&aacute;gina o secci&oacute;n a donde se desea que el visitante ingrese.</i> <br>
            NOTAS: <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Si hay m&aacute;s de un v&iacute;nculo a la misma p&aacute;gina, se usa el mismo tag.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Requiere que se cargue una secci&oacute;n.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Si se requiere utilizar en otro tag que no sea A (anchor) de HTML, utilizar el url dentro del HREF.</i> <br>
            <br>
            <a href='#'>[Ver Ejemplo]</a></td>
        </tr>
        <tr>
          <td class="answerCellBG"><textarea name="hrefsample" cols="100" rows="3"><?=$href ?>
      </textarea></td>
        </tr>
      </table>
      <br>
      <hr>
      <br>
      <table border='0' cellpadding='5' cellspacing='1' class='secTableBG' width='100%'>
        <tr class="mainTableBG">
          <td bgcolor='#009900'><strong class="secTableTitle">Tracking NO VIEW &nbsp; [GENERALES]</td>
        </tr>
        <tr>
          <td class="answerCellBG" valign="top"> UBICACI&Oacute;N: <i>Se coloca en la parte superior e inicial de una pieza de HTML.</i> <br>
            NOTAS: <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Solamente se coloca un tag por HTML.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; No requiere que se cargue una secci&oacute;n.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Sugerencia de texto: "Si no puedes ver esta pieza, por favor, haz click <a href='#'>aqu&iacute;</a>".</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; <font color='#FF0000'>OBLIGATORIO.</font></i> <br>
            <br>
            <a href='#'>[Ver Ejemplo]</a></td>
        </tr>
        <tr>
          <td class="answerCellBG"><textarea name="noviewsample" cols="100" rows="3"><?=$noview ?>
      </textarea></td>
        </tr>
      </table>
      <br>
      <br>
      <table border='0' cellpadding='5' cellspacing='1' class='secTableBG' width='100%'>
        <tr class="mainTableBG">
          <td bgcolor='#FF9900'><strong class="secTableTitle">Tracking VIEW o IMG &nbsp; [GENERALES]</td>
        </tr>
        <tr>
          <td class="answerCellBG" valign="top"> UBICACI&Oacute;N: <i>Se coloca en el c&oacute;digo HTML, justo antes del cierre del tag BODY.</i> <br>
            NOTAS: <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Solamente se coloca un tag por HTML.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Este es el tag que mide la cantidad de e-mails ABIERTOS del env&iacute;o.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; <font color='#FF0000'>OBLIGATORIO.</font></i> <br>
            <br>
            <a href='#'>[Ver Ejemplo]</a></td>
        </tr>
        <tr>
          <td class="answerCellBG"><textarea name="imgsample" cols="100" rows="3"><?=$img ?>
      </textarea></td>
        </tr>
      </table>
      <br>
      <br>
      <table border='0' cellpadding='5' cellspacing='1' class='secTableBG' width='100%'>
        <tr class="mainTableBG">
          <td bgcolor='#CC0000'><strong class="secTableTitle">Tracking UNSUSCRIBE o BAJA &nbsp; [GENERALES]</td>
        </tr>
        <tr>
          <td class="answerCellBG" valign="top"> UBICACI&Oacute;N: <i>Se coloca en la parte final de la pieza (footer), abajo del mensaje o contenido principal del e-mail.</i> <br>
            NOTAS: <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Solamente se coloca un tag por HTML.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Requiere se cargue la secci&oacute;n una &uacute;nica vez y ese se usa en todas las piezas.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Este es el tag que permitir&iacute;a las bajas de los clientes al programa.</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; Sugerencia de texto: "Si no deseas seguir recibiendo comunicaci&oacute;n de nosotros, puedes darte de baja haciendo click <a href='#'>aqu&iacute;</a>, o envianos un correo con tu solicitud a [Cuenta E-mail de ReplyTo de cada eCRM]".</i> <br>
            <i>&nbsp;&nbsp;&nbsp;&raquo; <font color='#FF0000'>OBLIGATORIO.</font></i> <br>
            <br>
            <a href='#'>[Ver Ejemplo]</a></td>
        </tr>
        <tr>
          <td class="answerCellBG"><textarea name="noviewsample" cols="100" rows="3"><?=$unsuscribe ?>
      </textarea></td>
        </tr>
      </table></td></tr>
    </table>
  </td>
  </tr>
</table>
          
          </td>
        </tr>
      </table></td>
  </tr>
</table>
<br>
<br>
<br>
<table width="750" border="0">
  <tr>
    <td align="right"><span class="copyright"><?= $copyright ?></span></td>
  </tr>
</table>
</body>
</html>
