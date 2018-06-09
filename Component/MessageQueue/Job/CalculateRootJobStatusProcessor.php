<?php

namespace Labudzinski\TestFrameworkBundle\Component\MessageQueue\Job;

use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Client\Message;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Client\MessagePriority;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Client\MessageProducerInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Client\TopicSubscriberInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Transport\MessageInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Transport\SessionInterface;
use Labudzinski\TestFrameworkBundle\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

class CalculateRootJobStatusProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var JobStorage */
    private $jobStorage;

    /** @var RootJobStatusCalculator */
    private $rootJobStatusCalculator;

    /** @var MessageProducerInterface */
    private $producer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param JobStorage               $jobStorage
     * @param RootJobStatusCalculator  $calculateRootJobStatusCase
     * @param MessageProducerInterface $producer
     * @param LoggerInterface          $logger
     */
    public function __construct(
        JobStorage $jobStorage,
        RootJobStatusCalculator $calculateRootJobStatusCase,
        MessageProducerInterface $producer,
        LoggerInterface $logger
    ) {
        $this->jobStorage = $jobStorage;
        $this->rootJobStatusCalculator = $calculateRootJobStatusCase;
        $this->producer = $producer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());

        if (! isset($data['jobId'])) {
            $this->logger->critical('Got invalid message');

            return self::REJECT;
        }

        $job = $this->jobStorage->findJobById($data['jobId']);
        if (! $job) {
            $this->logger->critical(sprintf('Job was not found. id: "%s"', $data['jobId']));

            return self::REJECT;
        }

        $isRootJobStopped = $this->rootJobStatusCalculator->calculate(
            $job,
            $data['calculateProgress'] ?? false
        );

        if ($isRootJobStopped) {
            $this->producer->send(
                Topics::ROOT_JOB_STOPPED,
                new Message(['jobId' => $job->getRootJob()->getId()], MessagePriority::HIGH)
            );
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CALCULATE_ROOT_JOB_STATUS];
    }
}
