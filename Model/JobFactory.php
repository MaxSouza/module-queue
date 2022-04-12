<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\JobInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\InvalidArgumentException;

class JobFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create job interface instance with specified parameters
     *
     * @param string $jobClass
     * @return JobInterface
     */
    public function create(string $jobClass): JobInterface
    {
        if ($jobClass !== JobInterface::class) {
            throw new InvalidArgumentException(
                "Class '$jobClass' is not supported to job interface."
            );
        }

        return $this->objectManager->create($jobClass);
    }
}
