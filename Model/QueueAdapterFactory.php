<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\JobInterface;
use Discorgento\Queue\Api\QueueAdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\InvalidArgumentException;

class QueueAdapterFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var QueueAdapterInterface[]
     */
    private array $adapterPool;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $adapterPool
     */
    public function __construct(ObjectManagerInterface $objectManager, array $adapterPool)
    {
        $this->objectManager = $objectManager;
        $this->adapterPool = $adapterPool;
    }

    /**
     * Create queue adapter instance with specified parameters
     *
     * @param string $type
     * @return QueueAdapterInterface
     */
    public function create(string $type): QueueAdapterInterface
    {
        if (empty($this->adapterPool[$type])) {
            throw new InvalidArgumentException(
                "Class '$type' is not supported to queue."
            );
        }

        return $this->objectManager->create($this->adapterPool[$type]);
    }
}
