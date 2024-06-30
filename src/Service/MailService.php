<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MailService
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $from,
        string $subject,
        string $htmlTemplate,
        array $context,
        string $to = 'ventalisburger@gmail.com'
    ): void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate)
            ->context($context);

        $this->mailer->send($email);
    }



    public function sendFactureEmail(Order $order, UserInterface $user): void
    {
        $context = [
            'order' => $order,
            'user' => $user,
        ];

        $this->sendEmail(
            'ventalisburger@gmail.com',
            'Facture de votre commande  #' . $order->getNumRef(),
            'emails/FactureEmails.html.twig',
            $context,
            $user->getEmail() // Send the invoice to the user's email address
        );
    }
}


