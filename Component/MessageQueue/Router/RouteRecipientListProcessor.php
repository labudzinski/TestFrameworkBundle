<?php
namespace Labudzinski\TestFrameworkBundle\Component\MessageQueue\Router;

use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Transport\MessageInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Transport\SessionInterface as TransportSession;

class RouteRecipientListProcessor implements MessageProcessorInterface
{
    /**
     * @var RecipientListRouterInterface
     */
    private $router;

    /**
     * @param RecipientListRouterInterface $router
     */
    public function __construct(RecipientListRouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, TransportSession $session)
    {
        $producer = $session->createProducer();
        foreach ($this->router->route($message) as $recipient) {
            $producer->send($recipient->getDestination(), $recipient->getMessage());
        }

        return self::ACK;
    }
}
