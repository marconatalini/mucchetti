<?php

namespace App\EventSubscriber;

use App\Entity\Permit;
use App\Entity\Stamping;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class StampingSubscriber implements EventSubscriberInterface
{
    public function __construct(readonly MailerInterface $mailer)
    {
    }

    public function onWorkflowMissedStampingCompleteSubmit($event): void
    {
        /** @var Stamping $stamping */
        $stamping = $event->getSubject();
        $mailSubject = sprintf("MANCATA TIMBRATURA di %s %s del %s",
            $stamping->getEmployee()->getFirstName(),
            $stamping->getEmployee()->getLastName(),
            $stamping->getMissedAt()->format('d/m/Y H:i'),
        );

        $email = (new TemplatedEmail())
            ->to(new Address(
                $stamping->getEmployee()->getParentUser()->getEmail(),
                $stamping->getEmployee()->getParentUser(),
            ))
            ->subject($mailSubject)
            ->htmlTemplate('emails/stamping/stamping.html.twig')
            ->context(['stamping' => $stamping,])
        ;

        $this->mailer->send($email);

    }



    public function onWorkflowMissedStampingCompleteApprove($event): void
    {
        /** @var Stamping $stamping */
        $stamping = $event->getSubject();
        $mailSubject = sprintf("MANCATA TIMBRATURA: %s %s del %s",
            $stamping->getEmployee()->getFirstName(),
            $stamping->getEmployee()->getLastName(),
            $stamping->getMissedAt()->format('d/m/Y H:i'),
        );

        $email = (new TemplatedEmail())
//            ->from('hello@example.com')
            ->to(new Address('personale@europrofiligroup.it', 'Giusy'))
            ->cc($stamping->getEmployee()->getEmail())
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($mailSubject)
//            ->text('Sending emails is fun again!')
//            ->html('<p>See Twig integration for better HTML integration!</p>');
            // path of the Twig template to render
            ->htmlTemplate('emails/stamping/approved.html.twig')
            ->context(['stamping' => $stamping,])
            ;

//        try {
        $this->mailer->send($email);
//        } catch (TransportExceptionInterface $e) {
//        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
//            'workflow.permit_request.entered.start' => 'onWorkflowMissedStampingEnteredStart',
            'workflow.missed_stamping.completed.submit' => 'onWorkflowMissedStampingCompleteSubmit',
            'workflow.missed_stamping.completed.approve' => 'onWorkflowMissedStampingCompleteApprove',
//            'workflow.missed_stamping.completed.reject' => 'onWorkflowMissedStampingCompleteReject',
        ];
    }
}
