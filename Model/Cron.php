<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Contracts\JobInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Cron
{
    protected LoggerInterface $logger;
    protected MessageCollectionFactory $messageCollectionFactory;
    protected MessageRepository $messageRepository;
    protected ObjectManagerInterface $objectManager;

    public function __construct(
        LoggerInterface $logger,
        MessageCollectionFactory $messageCollectionFactory,
        MessageRepository $messageRepository,
        ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
    }

    public function execute()
    {
        $messages = $this->messageCollectionFactory->create();
        /** @var Message */
        foreach ($messages as $message) {
            try {
                /** @var JobInterface */
                $job = $this->objectManager->create($message->getJobClass());
                $job->execute($message->getTarget(), $message->getAdditionalData());
            } catch (\Throwable $th) {
                $this->logger->error("{$message->getJobClass()} - {$th->getMessage()}", $message->getData());
            }

            $this->messageRepository->delete($message);
        }

        return $this;
    }
}