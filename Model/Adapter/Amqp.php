<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\Adapter;

use Discorgento\Queue\Api\QueueAdapterInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Amqp implements QueueAdapterInterface
{
    private const TOPIC_QUEUE_SYNCHRONIZATION = 'discorgento_amqp_queue';

    /**
     * @var OperationInterfaceFactory
     */
    private OperationInterfaceFactory $operationFactory;

    /**
     * @var PublisherInterface
     */
    private PublisherInterface $amqpPublisher;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var IdentityGeneratorInterface
     */
    private IdentityGeneratorInterface $identityGenerator;

    /**
     * Amqp construct
     *
     * @param OperationInterfaceFactory $operationFactory
     * @param PublisherInterface $amqpPublisher
     * @param SerializerInterface $serializer
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        PublisherInterface $amqpPublisher,
        SerializerInterface $serializer,
        IdentityGeneratorInterface $identityGenerator
    ) {
        $this->amqpPublisher = $amqpPublisher;
        $this->serializer = $serializer;
        $this->identityGenerator = $identityGenerator;
        $this->operationFactory = $operationFactory;
    }

    /**
     * @inheritDoc
     */
    public function publish(array $data = []): void
    {
        if (!$data) {
            return;
        }

        $dataOperation = [
            'data' => [
                'bulk_uuid' => $this->identityGenerator->generateId(),
                'topic_name' => self::TOPIC_QUEUE_SYNCHRONIZATION,
                'serialized_data' => $this->serializer->serialize($data),
                'status' => OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        $operation = $this->operationFactory->create($dataOperation);
        $this->amqpPublisher->publish(self::TOPIC_QUEUE_SYNCHRONIZATION, $operation);
    }

    /**
     * @inheritDoc
     */
    public function consumer(): void
    {
        // TODO: Implement consumer() method.
    }
}
