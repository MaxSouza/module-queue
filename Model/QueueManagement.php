<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

class QueueManagement
{
    /**
     * @var QueueAdapterFactory
     */
    private QueueAdapterFactory $adapterFactory;

    /**
     * @param QueueAdapterFactory $adapterFactory
     */
    public function __construct(QueueAdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Execute publish from queue adapter
     *
     * @param string $type
     * @param array $data
     * @return void
     */
    public function publish(string $type, array $data = []): void
    {
        try {
            $adapter = $this->adapterFactory->create($type);
            $adapter->publish($data);
        } catch (\Exception $e) {
            //@TODO add logs
        }
    }
}
