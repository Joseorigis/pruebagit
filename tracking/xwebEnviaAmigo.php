<?php

header( 'Expires: Sat, 01 Jan 2000 00:00:01 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

?>

<script>
function CheckRequiredFields() {
    var errormessage = new String();
    // Put field checks below this point.
    if((WithoutContent(document.register.email1.value))&&(WithoutContent(document.register.email2.value))&&(WithoutContent(document.register.email3.value)))
    	{ errormessage += "\n- Ingrese al menos un correo electronico!."; document.register.email1.focus();}
   
// Put field checks above this point.
if(errormessage.length > 2) {
	alert('Por favor: ' + errormessage);
	return false;
	}
	
return true;
} // end of function CheckRequiredFields()

function WithoutContent(ss) {
if(ss.length > 0) { return false; }
return true;
}

function validarEmail(valor) {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(valor)){   
   return (true)
  } else {
   alert("La dirección de email es incorrecta.!\n Debe tener la forma nombre@dominio.com");
   return (false);
  }
}
</script>

<?php
   $idUser    		= $_GET['idUser'];
   $idEnvio   		= $_GET['idEnvio'];
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Envia a un Amigo</title>
</head>

<body>
<form name="register" method="get" action="webEnviaAmigoSent.php"  onSubmit="return CheckRequiredFields()">
<table width="295" border="0" align="center">
  <tr>
    <td width="285">Introduce los e-mail a enviar.</td>
    
  </tr>
  <tr>
    <td>&nbsp;</td>
    
  </tr>
  <tr>
    <td>&nbsp;</td>
   
  </tr>
  <tr>
    <td>
      <label>E-mail:
        <input type="text" name="email1">
      </label>  </td>
    
  </tr>
  <tr>
    <td><label>E-mail:
        <input type="text" name="email2">
      </label></td>
   
  </tr>
  <tr>
    <td><label>E-mail:
      <input type="text" name="email3">
    </label></td>
  </tr>
  <tr>
    <td><input type="hidden" name="idEnvio" value="<?php echo $idEnvio;?>">
	<input type="hidden" name="idUser" value="<?php echo $idUser;?>">
	</td>
    
  </tr>
  <tr>
  
  <td colspan="2" align="center">
       <input type="submit" name="enviar" value="Enviar">  
	  
  </td>
  </tr>
</table>
</form>
</body>
</html>
