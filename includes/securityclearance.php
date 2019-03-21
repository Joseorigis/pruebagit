<?php
/**
*
* TYPE:
*	INCLUDE REFERENCE
*
* securityclearance.php
* 	Administración de privilegios y perfiles de seguridad.
*
* @version 
*
*/

// INIT
	// Acciones que requieren permisos de escritura en el perfil o privilegio
		$allowwrite = array("add", "update", "block", "unblock", "delete", "erase", "safe", "unsafe");
		
		
// SECURITY CHECK
	// SECURITY, perfiles con acceso a security [Profiles IN (1,2)]
		if ($_SESSION[$configuration['appkey']]['userprofileid'] > 2 && $module == "security") {
			$allowaccess = 0;
		}

	// READONLY USER [Profiles IN (5)]
		if ($_SESSION[$configuration['appkey']]['userprofileid'] == 5 && in_array($action, $allowwrite)) {
			$allowaccess = 0;
		}

	// MYACCOUNT & PASSWORDS
		if ($module == "security" && $action == "passwordchange") {
			$allowaccess = 1;
		}
		if ($module == "security" && $action == "passwordupdate") {
			$allowaccess = 1;
		}
		if ($module == "security" && $action == "passwordrecover") {
			$allowaccess = 1;
		}


// SECURITY SPECIAL FAETURES
	// NAME PRIVACY HIDING [Profiles IN (5)]
		if ($_SESSION[$configuration['appkey']]['userprofileid'] == 5) {
			$nameprivacy = 1;
		}

?>
