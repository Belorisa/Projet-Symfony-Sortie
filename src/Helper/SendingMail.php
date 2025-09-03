<?php

namespace App\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendingMail extends AbstractController
{

    public function __construct(private MailerInterface $mailer)
    {}
    public function send(string $to, string $subject,string $template, array $params, ): void
    {
        $email = (new Email())
            ->from('no-reply@sortie.fr')
            ->to($to)
            ->subject($subject)
            ->html($this->renderView($template, $params));

        $this->mailer->send($email);
    }
}