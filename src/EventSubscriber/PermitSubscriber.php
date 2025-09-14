<?php

namespace App\EventSubscriber;

use App\Entity\Permit;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class PermitSubscriber implements EventSubscriberInterface
{
    public function __construct(readonly MailerInterface $mailer)
    {
    }

    public function onWorkflowPermitRequestEnteredStart($event): void
    {
        /** @var Permit $permit */
        $permit = $event->getSubject();
        $mailSubject = sprintf("NUOVO PERMESSO da approvare: %s %s inizio %s",
            $permit->getEmployee()->getFirstName(),
            $permit->getEmployee()->getLastName(),
            $permit->getStartAt()->format('d/m/Y H:i'),
        );

        $email = (new TemplatedEmail())
            ->to($permit->getEmployee()->getParentUser()->getEmail())
            ->subject($mailSubject)
            ->htmlTemplate('emails/request.html.twig')
            ->context(['permit' => $permit,])
        ;

        $this->mailer->send($email);

    }

    public function onWorkflowPermitRequestCompleteReject($event): void
    {
        /** @var Permit $permit */
        $permit = $event->getSubject();
        $mailSubject = sprintf("Permesso RESPINTO: %s %s inizio %s",
            $permit->getEmployee()->getFirstName(),
            $permit->getEmployee()->getLastName(),
            $permit->getStartAt()->format('d/m/Y H:i'),
        );

        $email = (new TemplatedEmail())
            ->to($permit->getEmployee()->getEmail())
            ->subject($mailSubject)
            ->htmlTemplate('emails/reject.html.twig')
            ->context(['permit' => $permit,])
        ;

        $this->mailer->send($email);

    }


    public function onWorkflowPermitRequestCompleteApprove($event): void
    {
        /** @var Permit $permit */
        $permit = $event->getSubject();
        $mailSubject = sprintf("PERMESSO approvato: %s %s inizio %s",
            $permit->getEmployee()->getFirstName(),
            $permit->getEmployee()->getLastName(),
            $permit->getStartAt()->format('d/m/Y H:i'),
        );

        $email = (new TemplatedEmail())
//            ->from('hello@example.com')
            ->to()
            ->cc($permit->getEmployee()->getEmail())
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($mailSubject)
//            ->text('Sending emails is fun again!')
//            ->html('<p>See Twig integration for better HTML integration!</p>');
            // path of the Twig template to render
            ->htmlTemplate('emails/approved.html.twig')
            ->context(['permit' => $permit,])
            ;

//        try {
        $this->mailer->send($email);
//        } catch (TransportExceptionInterface $e) {
//        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.permit_request.entered.start' => 'onWorkflowPermitRequestEnteredStart',
            'workflow.permit_request.completed.approve' => 'onWorkflowPermitRequestCompleteApprove',
            'workflow.permit_request.completed.reject' => 'onWorkflowPermitRequestCompleteReject',
        ];
    }
}
