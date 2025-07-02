<?php
// src/Service/EmailService.php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Twig\Environment;

class Mailer
{
    private $mail;
    private $twig;
    public function __construct(Environment $twig)

    {

        $this->mail = new PHPMailer(false);

        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'];
        $this->mail->Password   = $_ENV['SMTP_PASSWORD'];
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = $_ENV['SMTP_PORT'];
        $this->mail->CharSet = 'UTF-8';
        $this->twig = $twig;
        $this->mail->setFrom('noreply@kaelian.dev', 'Hotel Neptune');
    }

    private function send($to, $toName, $subject, $body, $altBody)
    {

        $this->mail->addAddress($to, $toName);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $body;
        $this->mail->AltBody = $altBody;

        $this->mail->send();
    }

    public function sendConfirmEmail(string $name, string $email, string $token)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Plus qu&#39;une étape pour finaliser votre inscription';

            echo $token;

            $body = $this->twig->render('email/confirm.html.twig', [
                'name' => $name,
                'token' => str_split($token)
            ]);

            $altBody = 'Il ne vous reste qu&#39;une dernière étape pour finaliser la création de votre compte. Entrer le code suivant dans le formulaire de confirmation: ' . $token;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function sendAccountCreation(string $name, string $email, string $coupon)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Création de votre compte';

            $body = $this->twig->render('email/welcome.html.twig', [
                'name' => $name,
                'coupon' => $coupon
            ]);

            $altBody = 'Votre compte a été créé avec succès.';

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function sendResetPassword(string $name, string $email, string $token, string $id)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Réinitialisation de votre mot de passe';

            $body = $this->twig->render('email/resetPassword.html.twig', [
                'name' => $name,
                'reset_password_url' => 'http://localhost/resetpassword?token=' . $token . '&reset_user=' . $id

            ]);

            $altBody = 'Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien suivant pour continuer: ' . 'http://localhost/resetpassword?token=' . $token . '&reset_user=' . $id;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
