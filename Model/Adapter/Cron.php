<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\Adapter;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Api\JobInterface;
use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Api\QueueAdapterInterface;
use Discorgento\Queue\Model\JobFactory;
use Discorgento\Queue\Model\MessageFactory;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Cron implements QueueAdapterInterface
{
    /**
     * @var MessageFactory
     */
    private MessageFactory $messageFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var MessageCollectionFactory
     */
    private MessageCollectionFactory $messageCollectionFactory;

    /**
     * @var MessageRepositoryInterface
     */
    private MessageRepositoryInterface $messageRepository;

    /**
     * @var JobFactory
     */
    private JobFactory $jobFactory;
    
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Cron adapter construct
     *
     * @param MessageFactory $messageFactory
     * @param SerializerInterface $serializer
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param MessageRepositoryInterface $messageRepository
     * @param JobFactory $jobFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageFactory $messageFactory,
        SerializerInterface $serializer,
        MessageCollectionFactory $messageCollectionFactory,
        MessageRepositoryInterface $messageRepository,
        JobFactory $jobFactory,
        LoggerInterface $logger
    ) {
        $this->messageFactory = $messageFactory;
        $this->serializer = $serializer;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageRepository = $messageRepository;
        $this->jobFactory = $jobFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function publish(array $data = []): void
    {
        $message = $this->messageFactory->create()->addData([
            'job_class' => $data['jobClass'] ?? '',
            'target' => $data['target'] ?? '',
        ])->setAdditionalData($data['additionalData'] ?? []);

        if (!$this->alreadyQueued($message)) {
            $this->messageRepository->save($message);
        }
    }

    /**
     * @inheritDoc
     */
    public function consumer(): void
    {
        /** @var MessageInterface[] $messages */
        $messages = $this->messageCollectionFactory->create();

        foreach ($messages as $message) {
            try {
                /** @var JobInterface */
                $job = $this->jobFactory->create($message->getJobClass());
                $job->execute($message->getTarget(), $message->getAdditionalData());
                $this->messageRepository->delete($message);
            } catch (Throwable $th) {
                $errorMessage = "Job {$message->getJobClass()} failed: '{$th->getMessage()}'";
                $this->logger->error($errorMessage, [
                    'target' => $message->getTarget(),
                    'additional_data' => $message->getAdditionalData(),
                ]);
            }
        }
    }

    /**
     * Check if given message is already queued
     *
     * @param MessageInterface $message
     * @return bool
     */
    private function alreadyQueued(MessageInterface $message): bool
    {
        $encodedAdditionalData = $this->serializer->serialize($message->getAdditionalData());

        return $this->messageCollectionFactory->create()
                ->addFieldToFilter('job_class', $message->getJobClass())
                ->addFieldToFilter('target', $message->getTarget())
                ->addFieldToFilter('additional_data', $encodedAdditionalData)
                ->getSize() > 0;
    }
}
