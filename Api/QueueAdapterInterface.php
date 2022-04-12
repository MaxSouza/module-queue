<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

interface QueueAdapterInterface
{
    public const AMQP_QUEUE_TYPE = 'amqp';
    public const BASE_QUEUE_TYPE = 'cron';

    /**
     * Send data to queue
     *
     * @param array $data
     * @return void
     */
    public function publish(array $data = []): void;

    /**
     * @return mixed
     */
    public function consumer(): void;
}
