<?php
declare(strict_types=1);

namespace AppBundle\Listener;

use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowListener implements EventSubscriberInterface
{
    public function checkIfAlreadyValidated(Event $event)
    {
        /** @var \AppBundle\Entity\User $user */
        $user = $event->getSubject();

    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.workflow.enter.waiting_skill_check' => ['checkIfAlreadyValidated'],
        );
    }
}
