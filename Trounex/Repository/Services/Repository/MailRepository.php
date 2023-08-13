<?php

namespace Trounex\Repository\Services\Repository;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

trait MailRepository {
  /**
   * @var PHPMailer $mailer
   */
  private $mailer;

  private $data = [];
  /**
   * The mail repository construct
   * instance the PHPMailer class
   * and start the mail sending config
   */
  public function __construct () {
    $this->mailer = new PHPMailer (true);
  }

  /**
   * The mail repository uncought method fallback
   */
  public function __call ($method, $arguments) {
    $mailObjectPropertyName = ((string)$method);

    $mailObjectPropertyValue = isset ($arguments [0]) ? $arguments [0] : true;

    $phpMailerClassPropertyList = array_keys (get_class_vars (PHPMAiler::class));

    $phpMailerClassMethodList = get_class_methods (PHPMailer::class);

    if ($this->mailer instanceof PHPMailer
      && in_array ($mailObjectPropertyName, $phpMailerClassPropertyList)) {
      $this->data [$mailObjectPropertyName] = $mailObjectPropertyValue;
      $this->mailer->$mailObjectPropertyName = $mailObjectPropertyValue;
    }

    if (in_array ($method, $phpMailerClassMethodList)
      && preg_match ('/^(add|set|clear|is)/i', $method)) {
      call_user_func_array ([$this->mailer, $method], $arguments);
    }

    return $this;
  }

  /**
   * Property getter fallback
   */
  public function __get ($propertyName) {
    if (isset ($this->data [$propertyName])) {
      return $this->data [$propertyName];
    }
  }

  /**
   * Property setter fallback
   */
  public function __set ($propertyName, $propertyValue) {
    $this->data [$propertyName] = $propertyValue;
  }

  public function __toString () {
    return json_encode ($this->data);
  }
}
