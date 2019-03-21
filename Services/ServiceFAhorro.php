<?php
/*
 *	$Id: sslclient.php,v 1.1 2004/01/09 03:23:42 snichol Exp $
 *
 *	SSL client sample.
 *
 *	Service: SOAP endpoint
 *	Payload: rpc/encoded
 *	Transport: https
 *	Authentication: none
 */
require_once('/lib/nusoap.php');


function obj2array($obj) {
  $out = array();
  foreach ($obj as $key => $val) {
    switch(true) {
        case is_object($val):
         $out[$key] = obj2array($val);
         break;
      case is_array($val):
         $out[$key] = obj2array($val);
         break;
      default:
        $out[$key] = $val;
    }
  }
  return $out;
}

IF ($_SERVER['REQUEST_METHOD'] == "GET")
 {
	$login = isset($_GET['login']) ? $_GET['login'] : '';
	$password = isset($_GET['password']) ? $_GET['password'] : '';
	$sucursalid = isset($_GET['sucursalid']) ? $_GET['sucursalid'] : '2276';
	$sucursalcaja = isset($_GET['sucursalcaja']) ? $_GET['sucursalcaja'] : '999';
	$empleadoid = isset($_GET['empleadoid']) ? $_GET['empleadoid'] : '999';
	$numero = isset($_GET['numero']) ? $_GET['numero'] : '190001093346';
	$saldo = isset($_GET['saldo']) ? $_GET['saldo'] : '0';
}
IF ($_SERVER['REQUEST_METHOD'] == "POST")
 {
	$login = isset($_POST['login']) ? $_POST['login'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	$sucursalid = isset($_POST['sucursalid']) ? $_POST['sucursalid'] : '2276';
	$sucursalcaja = isset($_POST['sucursalcaja']) ? $_POST['sucursalcaja'] : '999';
	$empleadoid = isset($_POST['empleadoid']) ? $_POST['empleadoid'] : '999';
	$numero = isset($_POST['numero']) ? $_POST['numero'] : '190001093346';
	$saldo = isset($_POST['saldo']) ? $_POST['saldo'] : '0';}

	$login = 'FARMACIASAHORRO';
	$password = @'F@RmD314H0RUAT!';
	//$sucursalid = '1';
	//$sucursalcaja = '999';
	//$empleadoid = '999';
	//$numero = '190001093346';
	//$saldo = '1';

    $parametros=array();
    $usuario = array();
    $tarjeta = array();

    $usuario['Login']=$login;
    $usuario['Password']=$password;
    $usuario['SucursalId']=$sucursalid;
    $usuario['SucursalCaja']=$sucursalcaja;
    $usuario['EmpleadoId']=$empleadoid;

    $tarjeta['Numero']=$numero;
    $tarjeta['Saldo']=$saldo;

    $parametros['usuario'] = $usuario;
    $parametros['tarjeta'] = $tarjeta;

$client = new SoapClient('http://184.107.55.167/uat_clubenfabb/MJService.svc?WSDL',$parametros);



$res=$client->ConsultarSaldo($parametros);

$result = obj2array($res);

//echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
//echo print_r($result);

$tarjeta=$result['ConsultarSaldoResult']['Tarjeta'];
$mensaje=$result['ConsultarSaldoResult']['Mensaje'];


echo '<h2>ConsultarSaldo</h2>';
echo '<h3>Usuario</h3>';
echo '<pre>'.'Login: '.$login.'</pre>';
echo '<pre>'.'Password: '.$password.'</pre>';
echo '<pre>'.'SucursalId: '.$sucursalid.'</pre>';
echo '<pre>'.'SucursalCaja: '.$sucursalcaja.'</pre>';
echo '<pre>'.'EmpleadoId: '.$empleadoid.'</pre>';
echo '<h3>Tarjeta</h3>';
echo '<pre>'.'Numero: '.$numero.'</pre>';
echo '<pre>'.'Saldo: '.$saldo.'</pre>';


echo '<h2>ConsultarSaldoResult</h2>';
echo '<pre>'.'Tarjeta: '.$tarjeta['Numero'].'</pre>';
echo '<pre>'.'Saldo: '.$tarjeta['Saldo'].'</pre>';
echo '<pre>'.'Mensaje: '.$mensaje.'</pre>';

?>