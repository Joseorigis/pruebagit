<?php
	/*********************************************************************
	*
	*	Implementación de Conexión a PHPMailer 2012.
	*
	*   Origis.
	*	Ultima Modificación: 07/Dic/2012
	***********************************************************************/

  ///////////////////////////////////////
	// Definición de Inclusiones de otras librerías..
  //require_once('class.phpmailer.php');
  //require_once('class.smtp.php');
  require_once('PHPMailerAutoload.php');


  /**************************************************************************************************
		Definición de clase con funcionalidad customizada para conectividad a
		PHPMailer
  **************************************************************************************************/

	 class SendMail	{

      public function __construct ($conn){
        // Definición de inclusión de librería de configuracion
		global $configuration;
        //include_once('../configuration.php');

        // Instanciamos un objeto de la clase phpmailer
        $this->mail = new phpmailer();

				// Informacion de Conexion...
        $this->mail->SMTPDebug = 0;
        $this->mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth   = $configuration[$conn]['smtpauth'];
        $this->mail->SMTPSecure = $configuration[$conn]['smtpsecure'];
        $this->mail->Host       = $configuration[$conn]['host'];
        $this->mail->Port       = intval($configuration[$conn]['port']);
        $this->mail->Username   = $configuration[$conn]['username'];
        $this->mail->Password   = $configuration[$conn]['password'];

			}

      /*-----------------------------------------------------------------------------
				Método para el envio de Email de un destinatario, asunto, contenido, cabeceras, remitente, nombre del remitente, nombre del destinatario, cc, bcc, archivos adjuntos.

				@Return true si fue exitoso el envio si no lo fue un false .
			----------------------------------------------------------------------------------*/
      public function mail($_from="",$_fromname="",$_replyto="",$_to="",$_cc="",$_bcc="",$_subject="",$_body="",$_headers="",$_attachment="",$_ical=""){

        // Obtiene arreglo de cabecera
        if($_headers <> ""){
          $CustomHeader = explode("\r\n", $_headers);
          // Agregar cabeceras
          for($index = 0; $index < count($CustomHeader); $index++) {
            $result = trim($CustomHeader[$index]);
            if($result <> ""){
              $this->mail->AddCustomHeader($result);
            }
          }
        }

        // Agregar parametros de envío
        $this->mail->From = $_from;
        $this->mail->FromName = $_fromname;

        if ($_replyto <> "")
        {
          $replyto = explode(',', $_replyto);
          for($index = 0; $index < count($replyto); $index++) {
            $this->mail->AddReplyTo(trim($replyto[$index]),"");
          }
        }

        if ($_to <> "")
        {
          $to = explode(',', $_to);
          for($index = 0; $index < count($to); $index++) {
            $this->mail->AddAddress(trim($to[$index]), '');
          }
        }

        if ($_cc <> "")
        {
          $cc = explode(',', $_cc);
          for($index = 0; $index < count($cc); $index++) {
            $this->mail->AddCC(trim($cc[$index]),"");
          }
        }

        if ($_bcc <> "")
        {
          $bcc = explode(',', $_bcc);
          for($index = 0; $index < count($bcc); $index++) {
            $this->mail->AddBCC(trim($bcc[$index]),"");
          }
        }

        if ($_attachment <> "")
        {
          $attachment = explode(',', $_attachment);
          for($index = 0; $index < count($attachment); $index++) {
            $this->mail->AddAttachment($attachment[$index]);
          }
        }

        if ($_ical <> "")
        {
            $this->mail->Ical = $_ical;
        }

        $this->mail->Subject = $_subject;
        $this->mail->AltBody = "";
        $this->mail->IsHTML(true);
        $this->mail->MsgHTML($_body);
        $this->CharSet = "iso-8859-1";

        // Enviar Email
        if(!$this->mail->Send()) {
          $this->mail->ClearAddresses();
          $this->mail->ClearAttachments();
          return false;
        } else {
          $this->mail->ClearAddresses();
          $this->mail->ClearAttachments();
          return true;
        }
      }

      public function InfoError(){
          return $this->mail->ErrorInfo;
      }
   }

?>