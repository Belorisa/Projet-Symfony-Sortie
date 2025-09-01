<?php

namespace App\MessageHandler;
use App\Message\ReminderMessage;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsMessageHandler]
class SendReminderMessage
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private MailerInterface $mailer,
        private Environment $twig,
        private UserRepository $userRepository
    ){}

    public function __invoke(ReminderMessage $message)
    {
        $sortie = $this->sortieRepository->find($message->getIdSortie());
        $user = $this->userRepository->find($message->getIdUser());
        if(!$sortie || !$user){
            return;
        }

        if(!$sortie->getUsers()->contains($user)){
            return;
        }

        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($user->getEmail())
            ->subject('Rappel : Sortie dans 48h - ' . $sortie->getNom())
            ->html($this->twig->render('emails/reminder.html.twig', [
                'user' => $user,
                'sortie' => $sortie,
            ]));

            $this->mailer->send($email);

    }

}