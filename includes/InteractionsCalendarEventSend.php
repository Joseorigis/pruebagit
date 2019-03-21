<?php

// EMAIL APPOINTMENT

	// WARNINGS & ERRORS
		//ini_set('error_reporting', E_ALL&~E_NOTICE);
		//error_reporting(E_ALL);
		//ini_set('display_errors', '1');

	// INIT
		// Obtengo el nombre del script en ejecución para la recursividad
		$file = $_SERVER["SCRIPT_NAME"];
		$break = Explode('/', $file);
		$currentscript = $break[count($break) - 1]; 

		// Variables de entrada
		$CampaignSMTPHost = ini_get('SMTP');


	// CAMPAIGN CONTENT
			$CampaignSender 	= 'noreply@orveecrm.com';
			$CampaignSenderName = 'Agenda';
			$CampaignReplyTo 	= '';
			$CampaignContent 	= 'templates/InteractionsCalendarEventTemplate.html'; 
			$CampaignSubject 	= 'Nueva Actividad: |SUBJECTDATA| el |EVENTDATE| a las |EVENTTIME| hrs'; 
			
		// Extraemos los datos de la campaña
			$items = 0;
			$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$script."', 
								'view', 
								'crm', 
								'0', 
								'Interactions', 
								'CalendarFrom';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows();
			if ($items > 0) {
				$my_row=$dbconnection->get_row();
				$CampaignSender 	= trim($my_row['ParameterValue']);
				if ($CampaignSender == '') { $CampaignSender = 'noreply@orveecrm.com'; }
			}

		// Extraemos los datos de la campaña
			$items = 0;
			$query  = "EXEC ".$configuration['instanceprefix']."dbo.usp_app_ParametersManage
								'".$_SESSION[$configuration['appkey']]['userid']."', 
								'".$script."', 
								'view', 
								'crm', 
								'0', 
								'Interactions', 
								'CalendarFromName';";
			$dbconnection->query($query);
			$items = $dbconnection->count_rows();
			if ($items > 0) {
				$my_row=$dbconnection->get_row();
				$CampaignSenderName 	= trim($my_row['ParameterValue']);
				if ($CampaignSenderName == '') { $CampaignSenderName = 'Agenda'; }
			}


		// CAMPAIGN CONTENT INSTANCE & PERSONALIZATION
			// Contenido
			$CampaignCode 		= "OrveeCRM.".$CampaignId.".".$CampaignSentId.".".$AffiliationId.".".date("YmdHis");
			$CampaignCodeAuth 	= md5($CampaignCode);
			$CampaignCodeUnique = "-@id:".$CampaignCode."-";
			
			// EMAIL HEADERS
			$EmailMessage['Headers'] = "";
			$EmailMessage['Headers'] .= "X-OrveeCRMEmailSender: ".$currentscript."\r\n";
			$EmailMessage['Headers'] .= "X-OrveeCRMEmailID: ".$CampaignCode."\r\n";
			$EmailMessage['Headers'] .= "X-OrveeCRMEmailAuth: ".$CampaignCodeAuth."\r\n";
	
			// To, From & Subject del Email
			$EmailTo 		= $EventEmailTo;
			$EmailCc		= "";
			$EmailBcc 		= "";
	
			// EMAIL FROM & TO
			$EmailMessage['From'] 	  = $CampaignSender;
			$EmailMessage['FromName'] = $CampaignSenderName;
			$EmailMessage['To']   	  = $EmailTo;
			$EmailMessage['ReplyTo']  = $CampaignReplyTo;
			$EmailMessage['Cc']  	  = "";
			$EmailMessage['Bcc']  	  = "";
	
			// EMAIL SUBJECT					
			$EmailMessage['Subject'] = $CampaignSubject;		
			$EmailMessage['Subject'] = str_replace("|SUBJECTDATA|", $EventTitle, $EmailMessage['Subject']);
			$EmailMessage['Subject'] = str_replace("|EVENTDATE|", $EventDate, $EmailMessage['Subject']);
			$EmailMessage['Subject'] = str_replace("|EVENTTIME|", $EventTime." hrs", $EmailMessage['Subject']);
			
			// EMAIL CONTENT					
			$EmailMessage['Content'] = $CampaignContent;
			$EmailMessage['Body'] = implode('', file($EmailMessage['Content']));
			$EmailMessage['Body'] = str_replace("|ENVIOID|", $CampaignSentId, $EmailMessage['Body']);
			$EmailMessage['Body'] = str_replace("|CAMPANIAID|", $CampaignId, $EmailMessage['Body']);
			$EmailMessage['Body'] = str_replace("|EMAILID|", $CampaignCodeUnique, $EmailMessage['Body']);
			$EmailMessage['Body'] = str_replace("|USERID|", $AffiliationId, $EmailMessage['Body']);
			$EmailMessage['Body'] = str_replace("|EMAIL|", $EmailTo, $EmailMessage['Body']);
			// Tags en APP & TIME
			$EmailMessage['Body'] = str_replace("|APP|", $currentscript, $EmailMessage['Body']);
			$EmailMessage['Body'] = str_replace("|TIME|", date('d/m/Y')." ".date('H:i:s'), $EmailMessage['Body']);
			
			// Tags ADHOC
				$AppLink = '';
				$AppLink = strtolower(str_replace(getCurrentPageScript(), '', getCurrentPageURL()));
				$EmailMessage['Body'] = str_replace("|LINK|", $AppLink, $EmailMessage['Body']);
				// AFFILIATED
				$EmailMessage['Body'] = str_replace("|CARDNUMBER|", $CardNumber, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|NOMBRE|", $AffiliationName, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|USERNAME|", $AffiliationEmail, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|PASSWORD|", $AffiliationPassword, $EmailMessage['Body']);
		
				// CALENDAR EVENT
				$EmailMessage['Body'] = str_replace("|EVENTTYPE|", $EventType, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTWHO|", $EventWho, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTTITLE|", $EventTitle, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTDESC|", $EventDesc, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTDATE|", $EventDate, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTTIME|", $EventTime." hrs", $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTWHEN|", $EventWhen." hrs", $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTWHERE|", $EventWhere, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|EVENTPLACE|", $EventPlace, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|SCHEDULE|", $CalendarSchedule, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|CALENDARID|", $CalendarId, $EmailMessage['Body']);
				$EmailMessage['Body'] = str_replace("|CALENDARDATE|", $CalendarDate, $EmailMessage['Body']);
			
		
		// CAMPAIGN SEND
			// Interpretar respuesta para el OK		
			// Reintentos?
			$EmailMessageSent = 0;	
			$EmailMessageSentLog = "";
			$actionerrorid = 0;
						
			// Enviamos notificación de nuevo acceso
			$EmailMessageSent = sendAppEmailMessage($EmailMessage);
							
			if ($EmailMessageSent == 1) {
				$Response = 'OK;';
				$ResponseResult = 1;
			} else {
				$Response = 'PHPError;';
				$ResponseResult = 0;
			}
			
?>