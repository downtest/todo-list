<?php

namespace Framework\Services;


use Framework\Services\Interfaces\Service;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class Session
 * @package Framework\Services
 * @method static Mailer getInstance
 */
class Mailer extends Service
{
    /**
     * @var self
     */
    protected static $instance;

    protected PHPMailer $mailer;

    public array $from = ['support@listodo.ru', 'LISToDo.ru'];
    public ?string $subject = null;
    public ?string $html = null;
    public ?string $body = null;
    public ?string $altBody = null;

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet    = 'UTF-8';
//        $this->mailer->SMTPDebug  = SMTP::DEBUG_SERVER;           //Enable verbose debug output
        $this->mailer->isSMTP();                                 //Send using SMTP
        $this->mailer->Host       = 'smtp.yandex.ru';            //Set the SMTP server to send through
        $this->mailer->SMTPAuth   = true;                        //Enable SMTP authentication
        $this->mailer->Username   = 'support@listodo.ru';        //SMTP username
        $this->mailer->Password   = 'Q8A-aQC-Rmg-g82';           //SMTP password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        $this->mailer->Port       = 465;                         //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        $this->mailer->setFrom('support@listodo.ru', 'Поддержка LisToDo');
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addAddress(string $emailTo, ?string $name = ''): self
    {
        if ($name) {
            $this->mailer->addAddress($emailTo, $name);
        } else {
            $this->mailer->addAddress($emailTo);
        }

        return $this;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Exception
     */
    public function send()
    {
        if (!$this->subject) {
            throw new \Exception('Заполните subject(Тему) до отправки письма');
        }
        if (!$this->body && !$this->html) {
            throw new \Exception('Заполните body(Текст), либо html до отправки письма');
        }

        $this->mailer->Subject = $this->subject;

        if ($this->html) {
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->html;
        } else {
            $this->mailer->isHTML(false);
            $this->mailer->Body = $this->body;
        }

        if ($this->altBody) {
            $this->mailer->AltBody = $this->altBody;
        }

        return $this->mailer->send();
//        return true;
    }

    public function getLastError(): string
    {
        return $this->mailer->ErrorInfo;
    }
}
