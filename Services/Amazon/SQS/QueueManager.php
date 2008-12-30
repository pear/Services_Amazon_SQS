<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains a class for creating, managing, and deleting Amazon Simple Queue
 * Service (SQS) queues
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright 2008 Mike Brittain, silverorange
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * Amazon SQS client base class.
 */
require_once 'Services/Amazon/SQS.php';

/**
 * Queue class.
 */
require_once 'Services/Amazon/SQS/Queue.php';

/**
 * Class for creating, managing, and deleting Amazon Simple Queue Service
 * (SQS) queues
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_QueueManager extends Services_Amazon_SQS
{
    // {{{ listQueues()

    /**
     * Gets a list of SQS queues for the current account
     *
     * @param string $prefix optional. Only list queues whose name begins with
     *                       the given prefix. If not specified, all queues for
     *                       the account are returned.
     *
     * @return array an array of {@link Services_Amazon_SQS_Queue} objects.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function listQueues($prefix = null)
    {
        $params = array();

        $params['Action'] = 'ListQueues';

        if ($prefix) {
            $params['QueueNamePrefix'] = $prefix;
        }

        $response = $this->sendRequest($params);

        // get queues from response
        $queues = array();
        $xpath  = $response->getXPath();
        $nodes  = $xpath->query('//sqs:QueueUrl');

        foreach ($nodes as $node) {
            $url   = $xpath->evaluate('string(text())', $node);
            $queue = new Services_Amazon_SQS_Queue($url, $this->account, '',
                $this->request);

            $queues[] = $queue;
        }

        return $queues;
    }

    // }}}
    // {{{ createQueue()

    /**
     * Creates a new queue for the current account
     *
     * @param string  $name    the queue name.
     * @param integer $timeout optional. Timeout for message visibility
     *
     * @return Services_Amazon_SQS_Queue the new queue object or false if the
     *         queue could not be created.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if the queue name is
     *         not a valid queue name, if the queue was recently deleted or if
     *         the queue already exists and the visibility timeout value
     *         differs from the value on the existing queue.
     *
     * @throws Services_Amazon_SQS_InvalidTimeoutException if the provided
     *         <kbd>$timeout</kbd> is not in the valid range.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function createQueue($name, $timeout = null)
    {
        if (!$this->isValidQueueName($name)) {
            throw new Services_Amazon_SQS_InvalidQueueException('The queue ' .
                'name "' . $name . '" is not a valid queue name. Queue names ' .
                'must be 1-80 characters long and must consist only of ' .
                'alphanumeric characters, dashes or underscores.', 0, $name);
        }

        if ($timeout !== null && !$this->isValidVisibilityTimeout($timeout)) {
            throw new Services_Amazon_SQS_InvalidTimeoutException('The ' .
                'specified timeout falls outside the allowable range (0-7200)',
                0, $timeout);
        }

        $params = array();

        $params['Action']    = 'CreateQueue';
        $params['QueueName'] = $name;

        $params['DefaultVisibilityTimeout'] =
            ($timeout !== null) ? $timeout : 30;

        try {
            $response = $this->sendRequest($params);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getCode()) {
            case 'AWS.SimpleQueueService.QueueDeletedRecently':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $name . '" was deleted recently. Please wait ' .
                    '60 seconds after deleting a queue before creating a ' .
                    'queue of the same name.', 0, $name);

            case 'AWS.SimpleQueueService.QueueNameExists':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $name . '" already exists. To set a ' .
                    'different visibility timeout, use the ' .
                    'Services_Amazon_SQS_Queue::setAttribute() method.',
                    0, $name);

            default:
                throw $e;
            }
        }

        $xpath    = $response->getXPath();
        $queueUrl = $xpath->evaluate('string(//sqs:QueueUrl/text())');

        $queue = new Services_Amazon_SQS_Queue($queueUrl, $this->account, '',
            $this->request);

        return $queue;
    }

    // }}}
    // {{{ deleteQueue()

    /**
     * Deletes a queue
     *
     * All existing messages in the queue will be lost.
     *
     * @param Services_Amazon_SQS_Queue|string $queue either a queue object or
     *                                                the queue URL of the
     *                                                queue to be deleted.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function deleteQueue($queue)
    {
        if ($queue instanceof Services_Amazon_SQS_Queue) {
            $queue = strval($queue);
        }

        $params = array();

        $params['Action'] = 'DeleteQueue';

        $this->sendRequest($params, $queue);
    }

    // }}}
    // {{{ isValidQueueName()

    /**
     * Gets whether or not a queue name is valid for Amazon SQS
     *
     * Amazon SQS queue names must conform to the following rules:
     * - must be 1 to 80 ASCII characters
     * - must contain only alphanumeric characters, dashes (-), and
     *   underscores (_).
     *
     * @param string $name the queue name to check.
     *
     * @return boolean true if the provided queue name is a valid SQS queue
     *                 name, otherwise false.
     */
    protected function isValidQueueName($name)
    {
        $valid = true;

        if (preg_match('/^[A-Za-z0-9\-\_]{1,80}$/', $name) === 0) {
            $valid = false;
        }

        return $valid;
    }

    // }}}
}

?>
