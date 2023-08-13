<?php

namespace Trounex\Repository\Services;

use App\Utils\Env;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Modules\BackgroundJobs\Jobs\MailJob;
use App\Modules\BackgroundJobs\BackgroundJob;
use Trounex\Repository\Services\Repository\MailRepository;

trait MailServiceRepository {
  use MailRepository;

  public function send () {
    try {
      $this->mailer->send ();

      return true;
    } catch (Exception $e) {
      #exit ("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
    }

    return false;
  }

  public static function sendMail (array $mailData = [], $debugMode = SMTP::DEBUG_OFF) {
    $mail = new Mail;

    /*
      $mail = new PHPMailer();
      $mail->IsSMTP();
      $mail->CharSet="UTF-8";
      $mail->SMTPSecure = 'tls';
      $mail->Host = 'smtp.gmail.com';
      $mail->Port = 587;
      $mail->Username = 'MyUsername@gmail.com';
      $mail->Password = 'valid password';
      $mail->SMTPAuth = true;
    */

    # $template = file_get_contents (url ('/reg/account/confirm/mail?user_id=1&_NO-JS=True'));

    $mail
      ->SMTPDebug ($debugMode)                        // Disable/Enable verbose debug output
      ->isSMTP ()                                     // Send using SMTP
      ->Host (Env::Get ('SMTP_HOST'))                 // Set the SMTP server to send through
      ->SMTPAuth (true)                               // Enable SMTP authentication
      ->Username (Env::Get ('SMTP_USERNAME'))         // SMTP username
      ->Password (Env::Get ('SMTP_PASSWORD'))         // SMTP password
      ->CharSet (Env::Get ('SMTP_DEFAULT_CHARSET'))
      ->SMTPSecure ('tls' /* PHPMailer::ENCRYPTION_SMTPS */)      // Enable implicit TLS encryption
      ->Port (Env::Get ('SMTP_PORT'))                 // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

      // Recipients
      ->setFrom (Env::Get ('APP_DEFAULT_RECIPIENT_EMAIL_ADDRESS'), Env::Get ('APP_DEFAULT_RECIPIENT_EMAIL_NAME'), 0)
      ->addReplyTo (Env::Get ('APP_DEFAULT_REPLY_EMAIL_ADDRESS'), Env::Get ('APP_DEFAULT_REPLY_EMAIL_NAME'))     // Add a recipient
      // ->addAddress('luiskapemba@gmail.com', 'Hélder Luís Kapemba')               // Name is optional
      // ->addAddress('create.missiam@gmail.com', 'Create Missiam')
      // ->addCC('cc@example.com')
      // ->addBCC('bcc@example.com')

      // Attachments
      // ->addAttachment('/var/tmp/file.tar.gz')         // Add attachments
      // ->addAttachment('/tmp/image.jpg', 'new.jpg')    // Optional name

      //Content
      ->isHTML (true)                                  // Set email format to HTML
      // ->Subject ('Assuno da Mensagem - Enviada Via APP')
      // ->Body ($template)
      // ->AltBody ('This is the body in plain text for non-HTML mail clients')
      ;

    if (isset ($mailData ['Addresses'])
      && is_array ($mailData ['Addresses'])
      && count ($mailData ['Addresses']) >= 1) {
      /**
       * map the addresses list and add each of it
       * as a destination for the current email
       */
      foreach ($mailData ['Addresses'] as $address) {
        $addressEmail = $address [0];
        $addressName = isset ($address [1]) ? $address [1] : (
          preg_replace ('/@.*$/', '', $addressEmail)
        );

        if (filter_var ($addressEmail, FILTER_VALIDATE_EMAIL)) {
          $mail->addAddress ($addressEmail, $addressName);
        }
      }
    }

    if (isset ($mailData ['CC'])
      && is_array ($mailData ['CC'])
      && count ($mailData ['CC']) >= 1) {
      /**
       * map the addresses list and add each of it
       * as a declinatory for the current email
       */
      foreach ($mailData ['CC'] as $address) {
        $addressEmail = $address;

        if (filter_var ($addressEmail, FILTER_VALIDATE_EMAIL)) {
          $mail->addCC ($addressEmail);
        }
      }
    }

    if (isset ($mailData ['BCC'])
      && is_array ($mailData ['BCC'])
      && count ($mailData ['BCC']) >= 1) {
      /**
       * map the addresses list and add each of it
       * as a declinatory for the current email
       */
      foreach ($mailData ['BCC'] as $address) {
        $addressEmail = $address;

        if (filter_var ($addressEmail, FILTER_VALIDATE_EMAIL)) {
          $mail->addBCC ($addressEmail);
        }
      }
    }

    $mailDefaultProps = [
      'AltBody',
      'Subject',
      'Body'
    ];

    /**
     * Map the mail prop
     */
    foreach ($mailDefaultProps as $prop) {
      if (isset ($mailData [$prop]) &&
        is_string ($mailData [$prop])) {
        $mail->$prop ($mailData [$prop]);

        if ($prop === 'Body') {
          $mail->MsgHTML ($mailData [$prop]);
        }
      }
    }

    return $mail->send ();
  }

  public static function ScheduleEmail (array $mailData = []) {
    return self::AddToMailQueue ($mailData);
  }

  public static function AddToMailQueue (array $mailData = []) {
    BackgroundJob::QueueMail ($mailData);
  }
}
