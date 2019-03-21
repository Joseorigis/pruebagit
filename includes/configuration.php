<?php
/**
* configuration.php
* 	Archivo con las variables y parámetros iniciales para la aplicación.
*
* @author rgutierrez
* @created 20100804
* @version 20121211
* @comments 
*	+ Hay que configurar todas las variables posibles
*
*/

	// APPLICATION TIMEZONE
		date_default_timezone_set('America/Mexico_City');
		// http://www.php.net/manual/es/timezones.america.php


	// APPLICATION
		$configuration['appkey'] 		= "orbisportalmain";
		$configuration['apptitle'] 		= "OrveeCRM ";
		$configuration['appcopyright']  = "OrveeCRM by Origis &copy; ".date("Y");
		//$configuration['base_url'] 		= "http://apps.orveecrm.com/sanofi/";
		//$configuration['base_path']	= "C:\Inetpub\wwwroot\APPS\CRM\new\";
		$configuration['appversion'] 	= "v1.0";
		$configuration['licensenumber']	= "orveecrmlicense";
	
	
	// INSTANCE
		$configuration['instancekey'] 		= "orbisportalmain";
		$configuration['instancefirstname']	= "Origis";
		$configuration['instancelastname'] 	= "Portal Orbis";
		$configuration['instanceprefix'] 	= "";
	
	
	// TEMPLATES
		$configuration['templateuserblocked'] = "templates/UserBlocked.html";
		$configuration['templateusernew'] 	  = "templates/UserNew.html";
	
	
	// SUPERADMINISTRATOR
		$configuration['adminname']  	= "OrveeCRM Admin";
		$configuration['adminemail'] 	= "noreply@orveecrm.com";
		$configuration['adminreplyto'] 	= "helpdesk@orbisfarma.com.mx";
		$configuration['admincc']	 	= "";
		$configuration['adminbcc']	 	= "";
	
	
	// DATABASE
    /*
		$configuration['db0type'] 		= "sqlsrv";
		$configuration['db0host']		= "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db0name']       = "orveecrmmain";
		$configuration['db0username']	= "OrveeSecurity@va8n7ywa28";
		$configuration['db0password']	= "S#cur!typass";
		
		$configuration['db1type'] 		= "sqlsrv";
		$configuration['db1host']		= "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db1name']      	= "OrbisDatabase";
		$configuration['db1username'] 	= "OrveeUser@va8n7ywa28";
		$configuration['db1password'] 	= "0rv3#pass";
		
		$configuration['db2type']       = "sqlsrv";
		$configuration['db2host']       = "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db2name']       = "OrbisDatabase";
		$configuration['db2username']   = "OrveeUser@va8n7ywa28";
		$configuration['db2password']   = "0rv3#pass";	
	*/
    
		$configuration['db0type'] 		= "sqlsrv";
		$configuration['db0host']		= "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db0name']       = "orveecrmmain";
		$configuration['db0username']	= "OrveeSecurityLogin@va8n7ywa28";
		$configuration['db0password']	= "0rveeS3cur1ty0318";
		
		$configuration['db1type'] 		= "sqlsrv";
		$configuration['db1host']		= "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db1name']      	= "OrbisDatabase";
		$configuration['db1username'] 	= "OrbisAppLogin@va8n7ywa28";
		$configuration['db1password'] 	= "0rb!s4pp0318";
		
		$configuration['db2type']       = "sqlsrv";
		$configuration['db2host']       = "tcp:va8n7ywa28.database.windows.net,1433";
		$configuration['db2name']       = "OrbisDatabase";
		$configuration['db2username']   = "OrbisAppLogin@va8n7ywa28";
		$configuration['db2password']   = "0rb!s4pp0318";	
	
	// WEB SERVICES
		$configuration['webservice']	= "https://orbisws00.orbisfarma.com.mx/Transaccion.asmx";
		$configuration['webservicekey'] = "0";
	
	
	// SMTP
		// SMTP APP
		$configuration['smtpconnection0']['host']		= "smtp.gmail.com";
		$configuration['smtpconnection0']['port']	 	= 465;
		$configuration['smtpconnection0']['username'] 	= "noreply@orveecrm.com";
		$configuration['smtpconnection0']['password'] 	= "orveecrmpassword";
		$configuration['smtpconnection0']['smtpauth']	= true;
		$configuration['smtpconnection0']['smtpsecure'] = "ssl";
	
		// SMTP CRM INSTANCE 1
		$configuration['smtpconnection1']['host']		= "smtp.gmail.com";
		$configuration['smtpconnection1']['port']	 	= 465;
		$configuration['smtpconnection1']['username'] 	= "noreply@orveecrm.com";
		$configuration['smtpconnection1']['password'] 	= "orveecrmpassword";
		$configuration['smtpconnection1']['smtpauth']	= true;
		$configuration['smtpconnection1']['smtpsecure'] = "ssl";

		// SMTP CRM INSTANCE 2
		$configuration['smtpconnection2']['host']		= "smtp.gmail.com";
		$configuration['smtpconnection2']['port']	 	= 465;
		$configuration['smtpconnection2']['username'] 	= "noreply@orveecrm.com";
		$configuration['smtpconnection2']['password'] 	= "orveecrmpassword";
		$configuration['smtpconnection2']['smtpauth']	= true;
		$configuration['smtpconnection2']['smtpsecure'] = "ssl";

	
	// LDAP
		$configuration['ldapserver'] 	= "dominio.com.mx";
		$configuration['ldapprefijo'] 	= "dominio".chr(92);
	

?>
