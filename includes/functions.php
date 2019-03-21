<?php
/**
* functions.php
* 	Librería de funciones.
*
* @author
* @created
* @version
* @comments
*
*/



// ----------------------------------------------------
// SECURITY: begin
// ----------------------------------------------------

	/**
	*
	* doLogin()
	* Procesa el username y password enviado, para ver si es un usuario válido.
	*
	*/
	function doLogin($username, $password) {

		global $configuration, $dbsecurity;	// Global Configuration
		$functionresult = 0;
		$ldaploginresult = 99;

		// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];
			$page = getCurrentPageURL();

		// CHECK VARIABLES
			// Limpiamos variables enviadas... TBD:function?
			// TBD: verificar, si hay alguna de estos chars, marcar error o bloquearlo desde el input
			$usernametyped = $username;
			$username = str_replace("'", '',$username);
			$username = str_replace(";", '',$username);
			$username = str_replace("(", '',$username);
			$username = str_replace(")", '',$username);
			$username = str_replace("-", '',$username);
			$username = str_replace("/", '',$username);
			$username = str_replace(chr(92), '',$username);

			$passwordtyped = $password;
			$password = str_replace("'", '',$password);
			$password = str_replace(";", '',$password);
			$password = str_replace("(", '',$password);
			$password = str_replace(")", '',$password);
			$password = str_replace("-", '',$password);
			$password = str_replace("/", '',$password);
			$password = str_replace(chr(92), '',$password);


		// LOAD SECURITY SETTINGS
			//$securityincidentnotify = 'raulbg@origis.com.mx';
			$securityincidentnotify = $configuration['adminemail'];
			$securitylogtype = "ALL";

		// CHARACTER & SQL INJECTION CHECK & EXIT
			// Si se ingresaron caracteres inválidos, salimos con error...
			if ($usernametyped !== $username || $passwordtyped !== $password) {
				$functionresult = 101;
				return $functionresult;
			}

		// USER CHECK
			// Consultamos el status del usuario...
			$recordfound = 0;
			$password = encryptPassword($password);

			// Session Regenerate
			session_regenerate_id();

			$query = "EXEC dbo.usp_app_SecurityUserStatus
							'0',
							'".$configuration['appkey']."',
							'login',
							'".$username."',
							'".$password."',
							'".$configuration['appkey']."',
							'login',
							'".session_id()."',
							'server',
							'".$page."',
							'".$_SERVER['REMOTE_ADDR']."';";
			$dbsecurity->query($query);
			$recordfound = $dbsecurity->count_rows();

			// Si el usuario fue encontrado...
			if ($recordfound > 0) {

					// Tomamos los datos del usuario
					$userrow = $dbsecurity->get_row();

					// USER LDAP REQUIRED
					if ($userrow['Error'] == 115) {

						// USER LOGIN @ LDAP
						$ldaploginresult = doLoginLDAP($username, $passwordtyped);

						if ($ldaploginresult == 0) {
							$userrow['Error'] = 0;
							//echo "@OK";
						} else {
							// Asignamos el error de login LDAP
							$functionresult = $ldaploginresult;
							$userrow['Error'] = $functionresult;
							//echo "@Error:".$ldaploginresult;
						}

					}

					// USER OK
					// Si no hay error y el usuario es valido...
					if ($userrow['Error'] == 0) {

							// SESSION
							// Array para la session
							$sessionvars['userid']   			= $userrow['UserId'];
							$sessionvars['username']			= $userrow['Username'];
							$sessionvars['name']	 			= $userrow['Name'];
							$sessionvars['email'] 	 			= $userrow['Email'];
							$sessionvars['userstatusid'] 		= $userrow['UserStatusId'];
							$sessionvars['userstatus'] 			= $userrow['UserStatus'];
							$sessionvars['userprofileid']		= $userrow['UserProfileId'];
							$sessionvars['userprofile']	 		= $userrow['UserProfile'];
							$sessionvars['userpasswordchange'] 	= $userrow['UserPasswordChange'];
							$sessionvars['userpasswordexpire'] 	= $userrow['UserExpiryDays'];
							$sessionvars['userldap'] 			= $userrow['LDAPStatus'];
							$sessionvars['userjustlogin'] 		= true;
							$sessionvars['logtype']  		= $userrow['LogType'];
							$sessionvars['appurl'] 			= strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
							$sessionvars['apppath']			= strtolower(str_replace(getCurrentPageScript(), '', $_SERVER['SCRIPT_FILENAME']));
							$sessionvars['appprefix']  		= $configuration['instanceprefix'];
							// Iniciamos session
							$_SESSION[$configuration['appkey']] = $sessionvars;
							unset($sessionvars);

							// COOKIE
							// Activamos la cookie
							// Si la cookie no ha sido activada, es decir es LOGIN NORMAL...
//							if (!isset($_COOKIE[$configuration['appkey']])) {
//								setcookie($configuration['appkey']."[Username]",$usernametyped, time() + (3600*36));
//								setcookie($configuration['appkey']."[Password]",$passwordtyped, time() + (3600*36));
//								setcookie($configuration['appkey']."[Expire]",time() + (3600*36), time() + (3600*36));
//							} else {
//								// Si esta activa la cookie, venimos de AUTOLOGIN
//								// verificamos si ya expiró...
//								if ($_COOKIE[$configuration['appkey']]['Expire'] < time()) {
//										// La renovamos
//										setcookie($configuration['appkey']."[Username]",$usernametyped, time() + (3600*36));
//										setcookie($configuration['appkey']."[Password]",$passwordtyped, time() + (3600*36));
//										setcookie($configuration['appkey']."[Expire]",time() + (3600*36),time() + (3600*36));
//								}
//							}

					} else { // Si hubo error...

							$functionresult = $userrow['Error'];

								switch ($userrow['Error']) {
									case 104: // User Blocked

										// EMAIL FROM & TO
											$EmailMessage['From'] 	  = $configuration['adminemail'];
											$EmailMessage['FromName'] = $configuration['adminname'];
											$EmailMessage['To']   	  = $userrow['Email'];
											$EmailMessage['ReplyTo']  = $configuration['adminreplyto'];
											$EmailMessage['Cc']  	  = "";

											$EmailBcc = explode(",",$userrow['Mensaje']);
											$EmailsCopy = " ";
											for ($i=0;$i < count($EmailBcc); $i++) {
												$EmailsCopy .= "".$EmailBcc[$i].",";
											}
											$EmailMessage['Bcc']  	  = trim(substr($EmailsCopy, 0, -1));

										// EMAIL HEADERS
											$EmailMessage['Headers']  = "";
											$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$script."\r\n";
											$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$userrow['UserId'].".block@".$configuration['appkey']."\r\n";
											$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: block\r\n";

										// EMAIL CONTENT
											$EmailMessage['Subject'] = "Tu Acceso ha sido Bloqueado";
											$EmailMessage['Content'] = "templates/UserBlocked.html";
											$EmailMessage['Body']	 = implode('', file($EmailMessage['Content']));
											$EmailMessage['Body'] = str_replace("|USERNAME|", $userrow['Username'], $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|ACCESSURL|", str_replace(getCurrentPageScript(), '', getCurrentPageURL()), $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|USERSTATUS|", $userrow['UserStatusDescription'], $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|USERDATE|", date('d/m/Y H:i:s'), $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|APP|", strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL())), $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|SOURCE|", $configuration['appkey'], $EmailMessage['Body']);
											$EmailMessage['Body'] = str_replace("|MOREINFO|", '', $EmailMessage['Body']);

											// Enviamos notificación de nuevo acceso
											$EmailMessageSent = sendAppEmailMessage($EmailMessage);

										break;

									default:
										$functionresult = $userrow['Error'];
										break;
								}


					}

			} else {

				// USER NOT FOUND
				$functionresult = 108;

			}


		// NAVIGATION LOG
			// Obtenemos datos de la navegación
			$server = getCurrentServer();
			$page   = getCurrentPageURL();
			$baseurl = str_replace(getCurrentPageScript(), '', getCurrentPageURL());
			$referer = "BLANK";
			if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
			$querystring = "";
			if (isset($_SERVER['QUERY_STRING'])) { $querystring = $_SERVER['QUERY_STRING']; }

			// Si no hubo error, activamos la session en base de datos
			if ($userrow['Error'] == 0) {
					// Generamos el SESSION del USER
					$query = " EXEC dbo.usp_app_SecurityUserLogSession
										'".$userrow['UserId']."',
										'".$configuration['appkey']."',
									   'begin',
									   '".$userrow['UserId']."',
									   '".$configuration['appkey']."',
									   '".session_id()."',
									   '".$server['host']."',
									   '".$page."',
									   '".$_SERVER['REMOTE_ADDR']."';";
					$dbsecurity->query($query);
			}

			// Generamos el log de login
			$query = " EXEC dbo.usp_app_SecurityUserLogNavigation
								'".$userrow['UserId']."',
								'".$configuration['appkey']."',
							   'login',
							   '".$userrow['UserId']."',
							   '".$username."',
							   '".$password."',
							   '".$configuration['appkey']."',
							   '".session_id()."',
							   '".$server['host']."',
							   '".$page."',
							   '".$querystring."',
							   '".$_SERVER['REMOTE_ADDR']."',
							   '".$referer."',
							   '".$_SERVER['HTTP_USER_AGENT']."',
							   '".$userrow['Error']."'; ";
			$dbsecurity->query($query);

		return $functionresult;
	}


	/**
	*
	* doLogout()
	* Procesa el fin de una session en la aplicación.
	*
	*/
	function doLogout() {

		global $configuration, $dbsecurity;	// Global Configuration
		$functionresult = 66; // Código de LOGOUT

		$page = getCurrentPageURL();

		if (isset($_SESSION[$configuration['appkey']])) {
			// Generamos el logout o end del SESSION
			$query = " EXEC dbo.usp_app_SecurityUserLogSession
								'".$_SESSION[$configuration['appkey']]['userid']."',
								'".$configuration['appkey']."',
							   'end',
							   '".$_SESSION[$configuration['appkey']]['userid']."',
							   '".$configuration['appkey']."',
							   '".session_id()."',
							   '',
							   '".$page."',
							   '';";
			$dbsecurity->query($query);
		}

		// Matamos la SESSION
		session_unset();
		session_destroy();
		$_SESSION[] = array();

		// Matamos la COOKIE en caso de haber
//		if (isset($_COOKIE[$configuration['appkey']])) {
//			setcookie($configuration['appkey']."[Username]",'', time() - 3600);
//			setcookie($configuration['appkey']."[Password]",'', time() - 3600);
//			setcookie($configuration['appkey']."[Expire]",time() - 3600, time() - 3600);
//		}

		return $functionresult;
	}


	/**
	*
	* setNavigationLog()
	* Aplicar el LOG de navegación.
	*
	*/
	function setNavigationLog($type, $result, $navigationpage) {

		global $configuration, $dbsecurity;	// Global Configuration

		$functionresult = 0;

			// Obtengo el nombre del script en ejecución
				//$script = __FILE__;
				//$camino = get_included_files();
				//$scriptactual = $camino[count($camino)-1];
				$referer = "";
				if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
				$querystring = "";
				if (isset($_SERVER['QUERY_STRING'])) { $querystring = $_SERVER['QUERY_STRING']; }

			// NAVIGATION LOG
				// Obtenemos datos de la navegación
				$server = getCurrentServer();
				//$page   = getCurrentPageURL();
				//$baseurl = str_replace(getCurrentPageScript(), '', getCurrentPageURL());

				// Generamos el log de login
				$query = " EXEC dbo.usp_app_SecurityUserLogNavigation
									'".$_SESSION[$configuration['appkey']]['userid']."',
									'".$configuration['appkey']."',
								   'navigation',
								   '".$_SESSION[$configuration['appkey']]['userid']."',
								   '".$_SESSION[$configuration['appkey']]['username']."',
								   '',
								   '".$configuration['appkey']."',
								   '".session_id()."',
								   '".$server['host']."',
								   '".$_SESSION[$configuration['appkey']]['appurl'].$navigationpage."',
								   '".$querystring."',
								   '".$_SERVER['REMOTE_ADDR']."',
								   '".$referer."',
								   '".$_SERVER['HTTP_USER_AGENT']."',
								   '".$result."'; ";
				$dbsecurity->query($query);

		return $functionresult;
	}


	/**
	*
	* isSessionActive()
	* Procesa el fin de una session en la aplicación.
	*
	*/
	function isSessionActive() {

		global $configuration, $dbsecurity;	// Global Configuration
		$functionresult = 0;
		$sessionfound = 0;
		$sessionactive = 0;

		$page = getCurrentPageURL();

		// Si hay SESSION
		if (isset($_SESSION[$configuration['appkey']])) {

				// Checamos el status de la SESSION
				$query = " EXEC dbo.usp_app_SecurityUserLogSession
									'".$_SESSION[$configuration['appkey']]['userid']."',
									'".$configuration['appkey']."',
								   'check',
								   '".$_SESSION[$configuration['appkey']]['userid']."',
								   '".$configuration['appkey']."',
								   '".session_id()."',
								   '',
								   '".$page."',
								   '".$_SERVER['REMOTE_ADDR']."';";
				$dbsecurity->query($query);
				$sessionfound = $dbsecurity->count_rows();

				// Si hay registro...
				if ($sessionfound > 0) {

						// Tomamos los datos del registro
						$sessionrow = $dbsecurity->get_row();
						// Obtenemos el status de la SESSION actual
						$sessionactive = $sessionrow['SessionStatus'];
						// Si esta ACTIVA marcamos como activa
						if ($sessionactive == '1') {
								$functionresult = $sessionactive;
						}

				}

		}

		return $functionresult;
	}



	/**
	*
	* doLoginSwitch()
	* Procesa el username y password enviado, para ver si es un usuario válido.
	*
	*/
	function doLoginSwitch($userid, $username, $applicationkey) {

		global $configuration, $dbsecurity;	// Global Configuration
		$functionresult = 0;
		$ldaploginresult = 99;

		// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];
			$page = getCurrentPageURL();

		// CHECK VARIABLES
			// Limpiamos variables enviadas... TBD:function?
			// TBD: verificar, si hay alguna de estos chars, marcar error o bloquearlo desde el input
			$usernametyped = $username;
			$username = str_replace("'", '',$username);
			$username = str_replace(";", '',$username);
			$username = str_replace("(", '',$username);
			$username = str_replace(")", '',$username);
			$username = str_replace("-", '',$username);

			$useridtyped = $userid;
			$userid = str_replace("'", '',$userid);
			$userid = str_replace(";", '',$userid);
			$userid = str_replace("(", '',$userid);
			$userid = str_replace(")", '',$userid);
			$userid = str_replace("-", '',$userid);
			$password = $userid;
			$passwordtyped = $useridtyped;


		// LOAD SECURITY SETTINGS
			//$securityincidentnotify = 'raulbg@origis.com.mx';
			$securityincidentnotify = $configuration['adminemail'];
			$securitylogtype = "ALL";

		// 1. Validar contenido de las variables
		// 1.1. LOAD security parameters
		// 4. Parsear resultado y tipos de salida
		// 4.1. Attempts, Bloqueos, Not Found, Expiration,
		// 5. Obtener permisos

		// CHARACTER & SQL INJECTION CHECK & EXIT
			// Si se ingresaron caracteres inválidos, salimos con error...
			if ($usernametyped !== $username || $passwordtyped !== $password) {
				$functionresult = 101;
				return $functionresult;
			}

		// USER CHECK
			// Consultamos el status del usuario...
			$recordfound = 0;
			$query = "EXEC dbo.usp_app_SecurityUserStatus
							'0',
							'".$configuration['appkey']."',
							'switch',
							'".$username."',
							'".$password."',
							'".$configuration['appkey']."',
							'switch',
							'".session_id()."',
							'server',
							'".$page."',
							'".$_SERVER['REMOTE_ADDR']."';";
			$dbsecurity->query($query);
			$recordfound = $dbsecurity->count_rows();

			// Si el usuario fue encontrado...
			if ($recordfound > 0) {

					// Tomamos los datos del usuario
					$userrow = $dbsecurity->get_row();

					// USER LDAP REQUIRED
					if ($userrow['Error'] == 115) {

						// USER LOGIN @ LDAP
						$ldaploginresult = doLoginLDAP($username, $passwordtyped);

						if ($ldaploginresult == 0) {
							$userrow['Error'] = 0;
							//echo "@OK";
						} else {
							// Asignamos el error de login LDAP
							$functionresult = $ldaploginresult;
							$userrow['Error'] = $functionresult;
							//echo "@Error:".$ldaploginresult;
						}

					}

					// USER OK
					// Si no hay error y el usuario es valido...
					if ($userrow['Error'] == 0) {

							// SESSION
							// Array para la session
							$sessionvars['userid']   			= $userrow['UserId'];
							$sessionvars['username']			= $userrow['Username'];
							$sessionvars['name']	 			= $userrow['Name'];
							$sessionvars['email'] 	 			= $userrow['Email'];
							$sessionvars['userstatusid'] 		= $userrow['UserStatusId'];
							$sessionvars['userstatus'] 			= $userrow['UserStatus'];
							$sessionvars['userprofileid']		= $userrow['UserProfileId'];
							$sessionvars['userprofile']	 		= $userrow['UserProfile'];
							$sessionvars['userpasswordchange'] 	= $userrow['UserPasswordChange'];
							$sessionvars['userpasswordexpire'] 	= $userrow['UserExpiryDays'];
							$sessionvars['userldap'] 			= $userrow['LDAPStatus'];
							$sessionvars['userjustlogin'] 		= true;
							$sessionvars['logtype']  		= $userrow['LogType'];
							$sessionvars['appurl'] 			= strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
							$sessionvars['apppath']			= strtolower(str_replace(getCurrentPageScript(), '', $_SERVER['SCRIPT_FILENAME']));
							$sessionvars['appprefix']  		= $configuration['instanceprefix'];
							// Iniciamos session
							$_SESSION[$configuration['appkey']] = $sessionvars;
							unset($sessionvars);

							// COOKIE
							// Activamos la cookie
							// Si la cookie no ha sido activada, es decir es LOGIN NORMAL...
//							if (!isset($_COOKIE[$configuration['appkey']])) {
//								setcookie($configuration['appkey']."[Username]",$usernametyped, time() + (3600*36));
//								setcookie($configuration['appkey']."[Password]",$passwordtyped, time() + (3600*36));
//								setcookie($configuration['appkey']."[Expire]",time() + (3600*36), time() + (3600*36));
//							} else {
//								// Si esta activa la cookie, venimos de AUTOLOGIN
//								// verificamos si ya expiró...
//								if ($_COOKIE[$configuration['appkey']]['Expire'] < time()) {
//										// La renovamos
//										setcookie($configuration['appkey']."[Username]",$usernametyped, time() + (3600*36));
//										setcookie($configuration['appkey']."[Password]",$passwordtyped, time() + (3600*36));
//										setcookie($configuration['appkey']."[Expire]",time() + (3600*36),time() + (3600*36));
//								}
//							}

					} else { // Si hubo error...

							$functionresult = $userrow['Error'];

					}

			} else {

				// USER NOT FOUND
				$functionresult = 108;

			}


		// NAVIGATION LOG
			// Obtenemos datos de la navegación
			$server = getCurrentServer();
			$page   = getCurrentPageURL();
			$baseurl = str_replace(getCurrentPageScript(), '', getCurrentPageURL());
			$referer = "BLANK";
			if (isset($_SERVER['HTTP_REFERER'])) { $referer = $_SERVER['HTTP_REFERER']; }
			$querystring = "";
			if (isset($_SERVER['QUERY_STRING'])) { $querystring = $_SERVER['QUERY_STRING']; }

			// Si no hubo error, activamos la session en base de datos
			if ($userrow['Error'] == 0) {
					// Generamos el SESSION del USER
					$query = " EXEC dbo.usp_app_SecurityUserLogSession
										'".$_SESSION[$configuration['appkey']]['userid']."',
										'".$configuration['appkey']."',
									   'begin',
									   '".$userrow['UserId']."',
									   '".$configuration['appkey']."',
									   '".session_id()."',
									   '".$server['host']."',
									   '".$page."',
									   '".$_SERVER['REMOTE_ADDR'].";'";
					$dbsecurity->query($query);
			}

			// Generamos el log de login
			$query = " EXEC dbo.usp_app_SecurityUserLogNavigation
								'".$_SESSION[$configuration['appkey']]['userid']."',
								'".$configuration['appkey']."',
							   'login',
							   '".$userrow['UserId']."',
							   '".$username."',
							   '".$password."',
							   '".$configuration['appkey']."',
							   '".session_id()."',
							   '".$server['host']."',
							   '".$page."',
							   '".$querystring."',
							   '".$_SERVER['REMOTE_ADDR']."',
							   '".$referer."',
							   '".$_SERVER['HTTP_USER_AGENT']."',
							   '".$userrow['Error']."'; ";
			$dbsecurity->query($query);

		return $functionresult;
	}



	/**
	*
	* doLoginLDAP()
	* Procesa el fin de una session en la aplicación.
	*
	*/
	function doLoginLDAP($username, $password) {

		global $configuration, $dbconnection;	// Global Configuration
		$functionresult = 0;

		// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];


						// LDAP User & Password
						$ldaprdn  = $configuration['ldapprefijo'].$username;
						$ldappass = $password;

						// LDAP Connection
						$ldapconn = ldap_connect($configuration['ldapserver']);
						if (!($ldapconn)) {
							$functionresult = 116;
							//$mensajeerror .= "LDAP: Unable to connect to LDAP server!.<br>";
						} else {

							// Inicializamos
							$functionresult = 117;

							// LDAP Login
							$ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

							// LDAP Login Success
							if ($ldapbind) {
								//$numPass = 1;
								//$ldapsuccess = 1; // Flag para posteriores validaciones
								$functionresult = 0;
							} else {
								//$mensajeerror .= "LDAP: ".ldap_error($ldapconn)."<br>";
								//$numPass = 0;
								$functionresult = 117;
							}

						}

		return $functionresult;
	}

	/**
	*
	* isValidSecurityUsername()
	* Verificar que una contraseña  sea válida para el entorno de SECURITY.
	*
	*/
	function isValidSecurityUsername($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "$\S*(?=\S{8,20})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$";
			$regexp = "$\S*(?=\S{8,20})(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$"; // Removí al menos una mayuscula
//			$ = beginning of string
//			\S* = any set of characters
//			(?=\S{8,20}) = of at least length 8 thru 20
//			(?=\S*[a-z]) = containing at least one lowercase letter
//			(?=\S*[A-Z]) = and at least one uppercase letter
//			(?=\S*[\d]) = and at least one number
//			(?=\S*[\W]) = and at least a special character (non-word characters)
//			$ = end of the string

			//$plowers = "/[a-z]/";
			//$puppers = "/[A-Z]/";
			$plowers = "/[a-zA-Z]/";
			$puppers = "/[a-zA-Z]/";
			$pnumbers = "/[0-9]/";
			$pspecials = "/[*!#$._]/";

			// Content Validation
			$content = trim($content);

			// Password Validation
			//if (preg_match($regexp, $content)) {
			//	$functionresult = 1;
			//}

			// Alternate Validation
			$functionresult = 1;

				// lowers
				if (!preg_match($plowers, $content)) { $functionresult = 0; }
				// uppers
				if (!preg_match($puppers, $content)) { $functionresult = 0; }
				// numbers
				if (!preg_match($pnumbers, $content)) { $functionresult = 0; }
				// specials
				//if (!preg_match($pspecials, $content)) { $functionresult = 0; }
				// length
				if (strlen($content) < 8 || strlen($content) > 20) { $functionresult = 0; }

		return $functionresult;
	}

	/**
	*
	* isValidSecurityPassword()
	* Verificar que una contraseña  sea válida para el entorno de SECURITY.
	*
	*/
	function isValidSecurityPassword($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "$\S*(?=\S{8,20})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$";
			$regexp = "$\S*(?=\S{8,20})(?=\S*[a-zA-Z])(?=\S*[\d])(?=\S*[\W])\S*$"; // Removí al menos una mayuscula
//			$ = beginning of string
//			\S* = any set of characters
//			(?=\S{8,20}) = of at least length 8 thru 20
//			(?=\S*[a-z]) = containing at least one lowercase letter
//			(?=\S*[A-Z]) = and at least one uppercase letter
//			(?=\S*[\d]) = and at least one number
//			(?=\S*[\W]) = and at least a special character (non-word characters)
//			$ = end of the string

			//$plowers = "/[a-z]/";
			//$puppers = "/[A-Z]/";
			$plowers = "/[a-zA-Z]/";
			$puppers = "/[a-zA-Z]/";
			$pnumbers = "/[0-9]/";
			$pspecials = "/[*!#$._]/";

			// Content Validation
			$content = trim($content);

			// Password Validation
			//if (preg_match($regexp, $content)) {
			//	$functionresult = 1;
			//}

			// Alternate Validation
			$functionresult = 1;

				// lowers
				if (!preg_match($plowers, $content)) { $functionresult = 0; }
				// uppers
				if (!preg_match($puppers, $content)) { $functionresult = 0; }
				// numbers
				if (!preg_match($pnumbers, $content)) { $functionresult = 0; }
				// specials
				if (!preg_match($pspecials, $content)) { $functionresult = 0; }
				// length
				if (strlen($content) < 8 || strlen($content) > 20) { $functionresult = 0; }

		return $functionresult;
	}

	/**
	*
	* isValidSecurityEmail()
	* Verificar que un email sea válido para el entorno de SECURITY.
	*
	*/
	function isValidSecurityEmail($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";

			// Content Validation
			$content = trim($content);

			// Main Validation
			if (filter_var($content, FILTER_VALIDATE_EMAIL)) {
				$functionresult = 1;
			}

			// Alternate Validation
			//if (preg_match($regexp, $content)) {
			//	$functionresult = 1;
			//}

		return $functionresult;
	}

// ----------------------------------------------------
// SECURITY: end
// ----------------------------------------------------




// ----------------------------------------------------
// APPLICATION FUNCTIONS: begin
// ----------------------------------------------------

	/**
	*
	* sendAppEmailMessage()
	* Enviar un email de la aplicación.
	*
	*/
	function sendAppEmailMessage($EmailMessageArray) {

		global $configuration, $dbconnection;	// Global Configuration

		$functionresult = 0;

		$script = __FILE__;


		$SMTPLibrary = "includes/smtp/class.sendmail.php";

		if (!file_exists($SMTPLibrary)) {
			$SMTPLibrary = "../".$SMTPLibrary;
		}

		//require_once('includes/smtp/class.sendmail.php');
		require_once($SMTPLibrary);


		  // Instanciamos un objeto de la clase sendmail
		  $mail=new sendmail('smtpconnection0');

		  if(!isset($EmailMessageArray['ICal'])){
		  	$EmailMessageArray['ICal'] = "";
		  }

		  // mail(remitente,nombreremitente,responder,destinatario,[con copia],[con copia oculta],asunto,contenido,[cabeceras],[attachment])
		  if(!$mail->mail($EmailMessageArray['From'],
							$EmailMessageArray['FromName'],
							$EmailMessageArray['ReplyTo'],
							$EmailMessageArray['To'],
							$EmailMessageArray['Cc'],
							$EmailMessageArray['Bcc'],
							$EmailMessageArray['Subject'],
							$EmailMessageArray['Body'],
							$EmailMessageArray['Headers'],
							'',
                            $EmailMessageArray['ICal'])) {


					$EmailMessageArray['Headers'] = "To: ".$EmailMessageArray['To']."\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "From: ".$EmailMessageArray['From']."\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "Content-type: text/html; charset=iso-8859-1\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "MIME-Version: 1.0\r\n".$EmailMessageArray['Headers'];

		  		// IF hubo fallo, utilizamos la librería default...
					if (mail($EmailMessageArray['To'], $EmailMessageArray['Subject'], $EmailMessageArray['Body'], $EmailMessageArray['Headers'])) {

						$functionresult = 1;

					}else{

						$functionresult = 0;

					}

			} else {

				   $functionresult = 1;

			}

		 return $functionresult;
	}


	/**
	*
	* getLinkTitle()
	* Obtener el título del un vínculo reciente.
	*
	*/
	function getLinkTitle($link) {

		global $configuration, $dbconnection;	// Global Configuration

		$functionresult = "Vínculo";
		$sectionstitle = array("m", "s", "a");

		$script = __FILE__;

		// Si no hay parametros en el vínculo, entonces es HOME
		if (trim($link) == "") {
			$functionresult = "Inicio";
		} else {
			// Caso contrario, parseamos
			$functionresult = "";
			$linkpieces = explode("&", $link);
			for($i = 0; $i < count($linkpieces); $i++){
				$linkpiecesparts = explode("=", $linkpieces[$i]);
				if (in_array($linkpiecesparts[0], $sectionstitle)) {
					$functionresult .= ucwords($linkpiecesparts[1])." ";
				}
			}
			$functionresult = trim($functionresult);
		}

		return $functionresult;
	}


	/**
	*
	* getPageTitle()
	* Procesa el título de una página
	*
	*/
	function getPageTitle($module, $section, $action, $querystring) {

		global $configuration, $dbconnection;	// Global Configuration
		$functionresult = "";

		// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];

			$module  = strtolower($module);
			$section = strtolower($section);
			$action  = strtolower($action);

				switch ($module) {
					case "home":
						$functionresult = "Inicio";
						break;
					case "affiliation":
						$functionresult = "Afiliaci&oacute;n";
						break;
					case "interactions":
						$functionresult = "Interacciones";
						break;
					case "rules":
						$functionresult = "Reglas Negocio";
						break;
					case "rewards":
						$functionresult = "Recompensas";
						break;
					case "reports":
						$functionresult = "Reportes";
						break;
					case "security":
						$functionresult = "Seguridad";
						break;
					case "myaccount":
						$functionresult = "Mi Cuenta";
						break;
					default:
						$functionresult = ucwords($module);
				}


		return $functionresult;
	}


	/**
	*
	* getPageNavigationPath()
	* Procesa el path de navegación de una página
	*
	*/
	function getPageNavigationPath($module, $section, $action, $querystring) {

		global $configuration, $dbconnection;	// Global Configuration
		$functionresult = "<a href='index.php' title='Ir a Inicio'>Inicio</a>";

		// Obtengo el nombre del script en ejecución
			$script = __FILE__;
			$camino = get_included_files();
			$scriptactual = $camino[count($camino)-1];

			$module  = strtolower($module);
			$section = strtolower($section);
			$action  = strtolower($action);

				switch ($module) {
					case "home":
						$functionresult .= "";
						break;
					case "affiliation":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Afiliación'>Afiliaci&oacute;n</a>";
						break;
					case "interactions":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Interacciones'>Interacciones</a>";
						break;
					case "rules":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Reglas Negocio'>Reglas Negocio</a>";
						break;
					case "rewards":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Recompensas'>Recompensas</a>";
						break;
					case "reports":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Reportes'>Reportes</a>";
						break;
					case "security":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Seguridad'>Seguridad</a>";
						break;
					case "myaccount":
						$functionresult .= "&nbsp;>&nbsp;<a href='index.php?m=".$module."' title='Ir a Mi Cuenta'>Mi Cuenta</a>";
						break;
					default:
						$functionresult .= "&nbsp;>&nbsp;".ucwords($module);
				}

				if ($section != "") {
						$functionresult .= "&nbsp;>&nbsp;".ucwords($section)."&nbsp;".ucwords($action);
				}


		return $functionresult;
	}

	/**
	*
	* isValidAppParam()
	* Verificar que los parametros base (GET o POST) de la aplicación sean válidos
	* Parametros: [m|s|a]
	*
	*/
	function isValidAppParam($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/^[a-zA-Z0-9]+$/";

			// Content Validation
			$content = trim($content);

			// Regular Expression Validation
			if (preg_match($regexp, $content)) {
				$functionresult = 1;
			}

		return $functionresult;
	}

	/**
	*
	* setOnlyAppParamChars()
	* Ajusta una cadena, eliminando todo lo que no sea caracteres de parametros validos
	* Parametros: [m|s|a]
	*
	*/
	function setOnlyAppParamChars($content) {

		global $configuration;	// Global Configuration????

		$functionresult = '';	// result value handler

			// Regular Expression for funcion
			$regexp = "/[^a-zA-Z0-9]/";

			// Content Validation
            if(!is_array($content)) {
                $content = trim($content);

                $content = preg_replace($regexp, '', $content);

                $functionresult = trim(strtolower($content));
            }
		return $functionresult;
	}

// ----------------------------------------------------
// APPLICATION FUNCTIONS: end
// ----------------------------------------------------



// ----------------------------------------------------
// UTILITIES FUNCTIONS: begin
// ----------------------------------------------------

	/**
	*
	* sendEmailMessage()
	* Enviar un email en general.
	*
	*/
	function sendEmailMessage($EmailMessageArray) {

		global $configuration, $dbconnection;	// Global Configuration

		$functionresult = 0;

		$script = __FILE__;


		$SMTPLibrary = "includes/smtp/class.sendmail.php";

		if (!file_exists($SMTPLibrary)) {
			$SMTPLibrary = "../".$SMTPLibrary;
		}

		//require_once('includes/smtp/class.sendmail.php');
		require_once($SMTPLibrary);


		  // Instanciamos un objeto de la clase sendmail
		  $mail=new sendmail('smtpconnection1');

		  if(!isset($EmailMessageArray['ICal'])){
		  	$EmailMessageArray['ICal'] = "";
		  }

		  // mail(remitente,nombreremitente,responder,destinatario,[con copia],[con copia oculta],asunto,contenido,[cabeceras],[attachment])
		  if(!$mail->mail($EmailMessageArray['From'],
							$EmailMessageArray['FromName'],
							$EmailMessageArray['ReplyTo'],
							$EmailMessageArray['To'],
							$EmailMessageArray['Cc'],
							$EmailMessageArray['Bcc'],
							$EmailMessageArray['Subject'],
							$EmailMessageArray['Body'],
							$EmailMessageArray['Headers'],
							'',
                            $EmailMessageArray['ICal'])) {



					$EmailMessageArray['Headers'] = "To: ".$EmailMessageArray['To']."\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "From: ".$EmailMessageArray['From']."\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "Content-type: text/html; charset=iso-8859-1\r\n".$EmailMessageArray['Headers'];
					$EmailMessageArray['Headers'] = "MIME-Version: 1.0\r\n".$EmailMessageArray['Headers'];

		  		// IF hubo fallo, utilizamos la librería default...
					if (mail($EmailMessageArray['To'], $EmailMessageArray['Subject'], $EmailMessageArray['Body'], $EmailMessageArray['Headers'])) {

						$functionresult = 1;

					}else{

						$functionresult = 0;

					}

			} else {

				   $functionresult = 1;

			}

		 return $functionresult;
	}

	/**
	*
	* createRandomString()
	* Genera una cadena o contraseña aleatoria de la longitud solicitada.
	*
	*/
	function createRandomString($stringsize) {
		//QUITAMOS (L,l,I, 0,O) PARA EVITAR CONFUSIONES
		//$chars = "aAbBcCdDeEfFgGhHijJkKmMnNopPqQrRsStTuUvVwWxXyYzZ23456789*!#$._";
		$chars = "aAbBcCdDeEfFgGhHijJkKmMnNopPqQrRsStTuUvVwWxXyYzZ23456789._";
		$i = 0;
		$functionresult = "";

		while ($i < $stringsize) {
			$num = rand(0,strlen($chars)-1);
			$tmp = substr($chars, $num, 1);
			$functionresult = $functionresult . $tmp;
			$i++;
		}

		return $functionresult;
	}

	/**
	*
	* encryptPassword()
	* Encripta un password enviado.
	*
	*/
	function encryptPassword($password) {

		$functionresult = "";

		$functionresult = md5($password);

		return $functionresult;
	}

	/**
	*
	* getCurrentPage()
	* Obtiene la dirección o url de la página actual.
	*
	*/
	function getCurrentPage() {

		 $withparams = 0;

		 // Protocolo
		 $pageURL = "http";
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 //if ($_SERVER["SERVER_PORT"] != "80") {
		//	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 //} else {
		//	  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		// }
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		 // Verificamos si hay que enviar parametros del querystring
		 if ($withparams==0) {
			 $currentpage = explode("?",$pageURL);
			 $pageURL = $currentpage[0];
		 }
		 // Asignamos el valor a regresar
		 $functionresult = $pageURL;

		 return $functionresult;
	}

	/**
	*
	* getCurrentPageURL()
	* Obtiene la dirección o url de la página actual.
	*
	*/
	function getCurrentPageURL() {

		$functionresult = "";	// result value handler

		$withparams = 0;

		$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");

		$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));

		$port = ($port) ? ":".$_SERVER["SERVER_PORT"] : "";

		$pageURL = ($isHTTPS ? "https://" : "http://").$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];

		 // Verificamos si hay que enviar parametros del querystring
		 if ($withparams==0) {
			 $currentpage = explode("?",$pageURL);
			 $pageURL 	  = $currentpage[0];
		 }
		 // Asignamos el valor a regresar
		 $functionresult = $pageURL;

		 return $functionresult;
	}

	/**
	*
	* getCurrentServer()
	* Obtiene los datos del server name, ip y host.
	*
	*/
	function getCurrentServer() {

		// Inicializo
		$serverarray['name'] = "";
		$serverarray['ip']   = "";
		$serverarray['host'] = "";

		// Obtenemos los valores del servidor actual
		$serverarray['name'] = gethostbyaddr('127.0.0.1');
		$serverarray['ip']   = gethostbyname(gethostbyaddr('127.0.0.1'));
		$serverarray['host'] = strtoupper($serverarray['name']." [".$serverarray['ip']."]");

		// Asignamos el valor a regresar
		$functionresult = $serverarray;

		return $functionresult;
	}

	/**
	*
	* getCurrentPageScript()
	* Obtiene el nombre del script actual en ejecución.
	*
	*/
	function getCurrentPageScript() {
	 	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	}

	/**
	*
	* getLinkDomain()
	* Obtiene el dominio de un URL o liga
	*
	*/
	function getLinkDomain($url) {

		$nowww = str_replace ("www.","",$url);
		$domain = parse_url($nowww);

		if(!empty($domain["host"])) {
			return $domain["host"];
		} else {
			return $domain["path"];
		}

	}

	/**
	*
	* subtractDaysFromToday()
	* Resta días. TBD en donde se usa?
	*
	*/
	function subtractDaysFromToday($number_of_days) {
		$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

		$subtract = $today - (86400 * $number_of_days);

		//choice a date format here
		//return date("Ymd", $subtract);
		return date("d-M", $subtract);
	}

// ----------------------------------------------------
// UTILITIES FUNCTIONS: end
// ----------------------------------------------------



// ----------------------------------------------------
// VALIDATION FUNCTIONS: begin
// ----------------------------------------------------

	/**
	*
	* isSQLInjection()
	* Verifica si hay SQL Injection a la plataforma
	*
	*/
	function isSQLInjection() {

		global $configuration;	// Global Configuration????

		$functionresult = 0;

			// SQL Injection Check: BEGIN
				// GET
					// Obtenemos el query string
					$QueryStringHeader = "";
					if (isset($_SERVER['QUERY_STRING'])) { $QueryStringHeader = urldecode($_SERVER['QUERY_STRING']); }
						// Si hay comillas, redirigimos la ejecución
						if (strpos($QueryStringHeader, "'") !== false) {
							$functionresult = 1;
						}
				// POST
					// Cada variable de POST
					$CharacterFound = 0;
					foreach($_POST as $key => $value) {
					  	//echo "POST parameter '$key' has '$value'";
						// Si hay comillas, redirigimos la ejecución
                        if(!is_array($value)) {
                            if (strpos($value, "'") !== false) {
                                $CharacterFound = 1;
                                $functionresult = 1;
                            }
                        }
                        else{
                            $CharacterFound = 1;
                            $functionresult = 1;
                        }
					}
					// Si encontramos caracteres raros, detenemos la ejecución...
					if ($CharacterFound == 1) {
						unset($_POST);
					}
			// SQL Injection Check: END

		return $functionresult;

	}


	/**
	*
	* getRequestSource()
	* Verifica de donde viene la petición
	*
	*/
	function getRequestSource() {

		global $configuration;	// Global Configuration????

		$functionresult = "foreign";

		// REFERER ... la página previa
			$pagereferer = "NA";
			if (isset($_SERVER['HTTP_REFERER'])) {
				$referer 		= strtolower($_SERVER['HTTP_REFERER']);
				$urlparts 	= explode("?", $referer);
				$urlpieces  = explode("://", $urlparts[0]);
				if (count($urlpieces) > 1) {
					$pagereferer = $urlpieces[1];
				} else {
					$pagereferer = $urlpieces[0];
				}
			} else {
				$functionresult = "blank";
			}
			$pagereferer = strtolower($pagereferer);
			if ( (strrpos($pagereferer,"/") == strlen($pagereferer)-1) && (strlen($pagereferer) > 1) ) {
				$pagereferer = $pagereferer."index.php";
			}
			$urlparts 	 = explode("/", $pagereferer);
			$domainreferer = $urlparts[0];

		// CURRENT ... la página actual
			$pagecurrent = getCurrentPageURL();
			$urlpieces  = explode("://", getCurrentPageURL());
			if (count($urlpieces) > 1) {
				$pagecurrent = $urlpieces[1];
			} else {
				$pagecurrent = $urlpieces[0];
			}
			$pagecurrent = strtolower($pagecurrent);
			if ( (strrpos($pagecurrent,"/") == strlen($pagecurrent)-1) && (strlen($pagecurrent) > 1) ) {
				$pagecurrent = $pagecurrent."index.php";
			}
			$urlparts 	 = explode("/", $pagecurrent);
			$domaincurrent = $urlparts[0];


			//echo "antes:".$domainreferer."@<br>";
			//echo "ahora:".$domaincurrent."@<br>";


		// SAME DOMAIN
			if ($domainreferer == $domaincurrent) {
				$functionresult = "domain";
			}

		// SAME PAGE
			if ($pagereferer == $pagecurrent) {
				$functionresult = "page";
			}

			//echo $pagereferer."@".$pagecurrent;

		return $functionresult;

	}

	/**
	*
	* getActionAuth()
	* Genera un número de identificación de la operación a realizar
	*
	*/
	function getActionAuth() {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			$actionkey = ""; // key init

			// módulo de la acción
			$amodule = "";
			//if (strlen($module) > 1) {
			//	$amodule = substr($module, 0, 1);
			//}

			// identificador del usuario de la acción
			$auserid = "u0000";
			if (isset($_SESSION[$configuration['appkey']]['userid'])) {
				$auserid = $auserid.(string)$_SESSION[$configuration['appkey']]['userid'];
				$auserid = substr($auserid, strlen($auserid)-4, 4);
			}

			// cadena aleatoria
			//$akey = createRandomString(8);
			$arandomkey = createRandomString(4);

			// Key o llave de autorización
			//$actionkey = $amodule.".".session_id().".".$arandomkey.".".$auserid;
			$actionkey = session_id()."|".$arandomkey."|".$auserid;


			$functionresult = $actionkey;

		return $functionresult;
	}

	/**
	*
	* isValidActionAuth()
	* Verifica un número de identificación de la operación a realizar
	*
	*/
	function isValidActionAuth($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 1;	// result value handler

			$keyelements = explode("|", $content); // obtenemos los elementos

			if (count($keyelements) == 3) {

				// módulo de la acción
	//			$amodule = "";
	//			if (strlen($module) > 1) {
	//				$amodule = substr($module, 0, 1);
	//			}
	//			if ($amodule != $keyelements[0]) { $functionresult = 0; }

				// identificador del usuario de la acción
				$auserid = "u0000";
				if (isset($_SESSION[$configuration['appkey']])) {
					$auserid = $auserid.(string)$_SESSION[$configuration['appkey']]['userid'];
					$auserid = substr($auserid, strlen($auserid)-4, 4);
				}
				if ($auserid != $keyelements[2]) { $functionresult = 0; }

				// sessionid
				if (session_id() != $keyelements[0]) { $functionresult = 0; }
			} else {

				$functionresult = 0;

			}


		return $functionresult;
	}

	/**
	*
	* isValidEmail()
	* Verificar que un email sea válido.
	*
	*/
	function isValidEmail($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";

			// Content Validation
			$content = trim($content);

			// Main Validation
			if (filter_var($content, FILTER_VALIDATE_EMAIL)) {
				$functionresult = 1;
			}

			// Alternate Validation
			//if (preg_match($regexp, $content)) {
			//	$functionresult = 1;
			//}

		return $functionresult;
	}

	/**
	*
	* isValidEmailList()
	* Verificar que una lista de emails sea válida.
	*
	*/
	function isValidEmailList($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";

			// Content Validation
			$content = trim($content);
			$content = str_replace(" ", "", $content);

			// Content Split for each email
			$pieces = explode(",", $content);

			// Check every email
			$piecesvalids = 0;
			foreach($pieces as $piece){

				// Main Validation
				if (filter_var($piece, FILTER_VALIDATE_EMAIL)) {
					$piecesvalids = $piecesvalids + 1;
				}

				// Alternate Validation
				//if (preg_match($regexp, $content)) {
				//	$functionresult = 1;
				//}

			}

			// If total == valids then OK
			if (count($pieces) == $piecesvalids) {
				$functionresult = 1;
			}

		return $functionresult;
	}

	/**
	*
	* isValidURL()
	* Verificar que un URL sea válido.
	*
	*/
	function isValidURL($content) {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/";

			// Content Validation
			$content = trim($content);

			// Main Validation
			if (filter_var($content, FILTER_VALIDATE_URL)) {
				$functionresult = 1;
			}

			// Alternate Validation
			//if (preg_match($regexp, $content)) {
			//	$functionresult = 1;
			//}

		return $functionresult;
	}

	/**
	*
	* isValidNumber()
	* Verificar que un número sea válido acorde al tipo enviado
	*
	*/
	function isValidNumber($content, $contenttype = "") {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// To upper to avoid case sensitive
			$contenttype = strtoupper($contenttype);

			// Regular Expression for funcion
			$regexp = "/^[0-9]+$/";

			// Content Validation
			$content = trim($content);

			// Regular Expression Validation
			if (preg_match($regexp, $content)) {
				$functionresult = 1;

				// phone number
				if ($contenttype == "PHONE" && strlen($content) !== 10) {
					$functionresult = 0;
				}

				// cardnumber ean13
				if ($contenttype == "EAN13" && strlen($content) !== 13) {
					$functionresult = 0;
				}

				// cardnumber ean8
				if ($contenttype == "EAN8" && strlen($content) !== 8) {
					$functionresult = 0;
				}

			}

		return $functionresult;
	}

	/**
	*
	* isValidDate()
	* Verificar que una fecha sea válida
	* Formato: YYYYMMDD | dd/mm/yyyy | mm/dd/yyyy
	*
	*/
	function isValidDate($content, $contenttype = "") {

		global $configuration;	// Global Configuration????

		$functionresult = 0;	// result value handler

			// Regular Expression for funcion
			$regexp = "/^[0-9]+$/";

			// Content Validation
			$content = trim($content);


			if ($contenttype == "dd/mm/yyyy") {
				$content = str_replace("/", "", $content);
				$content = substr($content, 4, 4).substr($content, 2, 2).substr($content, 0, 2);
			}

			if ($contenttype == "mm/dd/yyyy") {
				$content = str_replace("/", "", $content);
				$content = substr($content, 4, 4).substr($content, 0, 2).substr($content, 2, 2);
			}

				// Extraemos los parametros por separado
				$year  = "0000";
				$month = "00";
				$day   = "00";
				if (strlen($content) == 8) {
					$year  = substr($content, 0, 4);
					$month = substr($content, 4, 2);
					$day   = substr($content, 6, 2);
				}


			// Regular Expression Validation
			if (preg_match($regexp, $content)) {
				//$functionresult = 1;

				// Date Validation
				if (checkdate($month, $day, $year )) {
					$functionresult = 1;
				}

			}

		return $functionresult;
	}

	/**
	*
	* setOnlyNumbers()
	* Ajusta una cadena, eliminando todo lo que no sea números
	*
	*/
	function setOnlyNumbers($content) {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			// Regular Expression for funcion
			$regexp = "/[^0-9]/";

			// Content Validation
			$content = trim($content);

			$content = preg_replace($regexp, "", $content);

			$content = trim($content);

			$functionresult = $content;

		return $functionresult;
	}

	/**
	*
	* setOnlyLetters()
	* Ajusta una cadena, eliminando todo lo que no sea letras
	*
	*/
	function setOnlyLetters($content) {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			// Regular Expression for funcion
			//$regexp = "/[^a-zA-Z ]/";
			$regexp = "/[^a-zA-Z ñÑ]/";

			// Content Validation
			$content = trim($content);

				// Acentos
				$content = preg_replace("/[áàâãª]/",  "a", $content);
				$content = preg_replace("/[ÁÀÂÃ]/",   "A", $content);
				$content = preg_replace("/[éèê]/",    "e", $content);
				$content = preg_replace("/[ÉÈÊ]/",    "E", $content);
				$content = preg_replace("/[íìî]/",    "i", $content);
				$content = preg_replace("/[ÍÌÎ]/",    "I", $content);
				$content = preg_replace("/[óòôõº0]/", "o", $content);
				$content = preg_replace("/[ÓÒÔÕ0]/",  "O", $content);
				$content = preg_replace("/[úùû]/",    "u", $content);
				$content = preg_replace("/[ÚÙÛ]/",    "U", $content);
				//$content = preg_replace("/[ñ]/",      "n", $content);
				//$content = preg_replace("/[Ñ]/",      "N", $content);

			$content = preg_replace($regexp, "", $content);

			$content = trim($content);

			$functionresult = $content;

		return $functionresult;
	}

	/**
	*
	* setOnlyName()
	* Ajusta una cadena, eliminando todo lo que no sea letras para un nombre
	*
	*/
	function setOnlyName($content) {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			// Regular Expression for funcion
			//$regexp = "/[^a-zA-Z ]/";
			$regexp = "/[^a-zA-Z ñÑ]/";

			// Content Validation
			$content = trim($content);

				// Acentos
				$content = preg_replace("/[áàâãª]/",  "a", $content);
				$content = preg_replace("/[ÁÀÂÃ]/",   "A", $content);
				$content = preg_replace("/[éèê]/",    "e", $content);
				$content = preg_replace("/[ÉÈÊ]/",    "E", $content);
				$content = preg_replace("/[íìî]/",    "i", $content);
				$content = preg_replace("/[ÍÌÎ]/",    "I", $content);
				$content = preg_replace("/[óòôõº0]/", "o", $content);
				$content = preg_replace("/[ÓÒÔÕ0]/",  "O", $content);
				$content = preg_replace("/[úùû]/",    "u", $content);
				$content = preg_replace("/[ÚÙÛ]/",    "U", $content);
				//$content = preg_replace("/[ñ]/",      "n", $content);
				//$content = preg_replace("/[Ñ]/",      "N", $content);

			$content = preg_replace($regexp, "", $content);

			//$content = trim(strtoupper($content));
			$content = trim($content);

			$functionresult = $content;

		return $functionresult;
	}

	/**
	*
	* setOnlyText()
	* Ajusta una cadena, eliminando todo lo que no sea texto
	*
	*/
	function setOnlyText($content) {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			// Content Validation
			$content = trim($content);

				// Acentos
				$content = str_replace("'", "", $content);
				$content = str_replace('"', "", $content);
				$content = str_replace("--", "", $content);
				$content = str_replace("/", "", $content);
				$content = str_replace("<", "", $content);
				$content = str_replace(">", "", $content);
				$content = str_replace(chr(8), "", $content);
				$content = str_replace(chr(9), "", $content);
				$content = str_replace(chr(11), "", $content);
				//$content = str_replace(chr(13), "", $content);
				$content = str_replace(chr(92), "", $content);

			$content = trim($content);

			$functionresult = $content;

		return $functionresult;
	}

	/**
	*
	* setOnlyCharactersValid()
	* Ajusta una cadena, eliminando todo lo mínimo indispensable
	*
	*/
	function setOnlyCharactersValid($content) {

		global $configuration;	// Global Configuration????

		$functionresult = "";	// result value handler

			// Content Validation
        if(!is_array($content)) {
			$content = trim($content);

            // Acentos
            $content = str_replace("'", "", $content);
            $content = str_replace('"', "", $content);
            $content = str_replace("--", "", $content);
            //$content = str_replace("/", "", $content);
            $content = str_replace("<", "", $content);
            $content = str_replace(">", "", $content);
            $content = str_replace(chr(8), "", $content);
            $content = str_replace(chr(9), "", $content);
            $content = str_replace(chr(11), "", $content);
            //$content = str_replace(chr(13), "", $content);
            //$content = str_replace(chr(92), "", $content);

			$content = trim($content);

			$functionresult = $content;
        }

		return $functionresult;
	}

// ----------------------------------------------------
// VALIDATION FUNCTIONS: end
// ----------------------------------------------------


?>
