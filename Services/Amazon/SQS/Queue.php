<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains a class representing a queue in the Amazon Simple Queue
 * Service (SQS)
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright 2008 Mike Brittain, Amazon.com, silverorange
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
 *   Portions of this code were taken from the Amazon SQS PHP5 Library which
 *   is distributed under the Apache 2.0 license
 *   (http://aws.amazon.com/apache2.0).
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 Amazon.com, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * Exception classes.
 */
require_once 'Services/Amazon/SQS/Exceptions.php';

/**
 * Amazon SQS client base class.
 */
require_once 'Services/Amazon/SQS.php';

/**
 * A queue in the Amazon SQS
 *
 * This class allows sending objects to and receiving objects from a queue on
 * the Amazon SQS. Use the queue URL provided by Amazon through the list or
 * creating methods when instantiating this class.
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 Amazon.com, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_Queue extends Services_Amazon_SQS
{
    // {{{ private properties

    /**
     * The URL of this queue provided by Amazon when the queue was created
     *
     * @var string
     */
    private $_queueUrl;

    // }}}
    // {{{ __construct()

    /**
     * Creates a PHP SQS queue object
     *
     * Queue objects are created with the full URL because Amazon reserves the
     * right to change the URL scheme for queues created in the future. Always
     * use the full queue URL.
     *
     * @param string                             $queueUrl        the URL of
     *        this queue.
     *
     * @param Services_Amazon_SQS_Account|string $accessKey       either a
     *        {@link Services_Amazon_SQS_Account} object or a string containing
     *        the SQS access key for an account.
     *
     * @param string                             $secretAccessKey if the first
     *        parameter is an account object, this parameter is ignored.
     *        Otherwise, this parameter is required and is the secret access
     *        key for the SQS account.
     *
     * @param HTTP_Request2                      $request         optional. The
     *        HTTP request object to use. If not specified, a HTTP request
     *        object is created automatically.
     */
    public function __construct($queueUrl, $accessKey, $secretAccessKey = '',
        HTTP_Request2 $request = null)
    {
        parent::__construct($accessKey, $secretAccessKey, $request);
        $this->_queueUrl = $queueUrl;
    }

    // }}}
    // {{{ __toString()

    /**
     * Gets a string representation of this queue
     *
     * Specifically, this returns the queue URL of this queue.
     *
     * @return string the URL of this queue.
     */
    public function __toString()
    {
        return $this->_queueUrl;
    }

    // }}}
    // {{{ send()

    /**
     * Sends a message to this queue
     *
     * @param string $message the message to put in this queue.
     *
     * @return string the message id of the message.
     *
     * @throws Services_Amazon_SQS_InvalidMessageException if the message
     *         contains characters outside the allowed set or if the message
     *         size is greater than 8192 bytes.
     *
     * @throws Services_Amazon_SQS_ChecksumException if the received checksum
     *         from Amazon does not match the calculated checksum of the
     *         sent message.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function send($message)
    {
        $params = array();

        $params['Action']      = 'SendMessage';
        $params['MessageBody'] = $message;

        try {
            $response = $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'InvalidMessageContents':
                throw new Services_Amazon_SQS_InvalidMessageException('The ' .
                    'message contains characters outside the allowed set.', 0,
                    $message);

            case 'MessageTooLong':
                throw new Services_Amazon_SQS_InvalidMessageException('The ' .
                    'message size can not exceed 8192 bytes.', 0, $message);

            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            default:
                throw $e;
            }
        }

        $messages     = array();
        $xpath        = $response->getXPath();
        $messageNodes = $xpath->query('//sqs:Message');
        $expectedMd5  = md5($message);

        $id  = $xpath->evaluate('string(//sqs:MessageId/text())');
        $md5 = $xpath->evaluate('string(//sqs:MD5OfMessageBody/text())');

        if ($md5 !== $expectedMd5) {
            throw new Services_Amazon_SQS_ChecksumException('Message ' .
                'body was not received by Amazon correctly. Expected ' .
                'MD5 was: "' . $expectedMd5 . '", but received MD5 was: "' .
                $md5 .'".', 0, $id);
        }

        return $id;
    }

    // }}}
    // {{{ receive()

    /**
     * Retrieves one or more messages from this queue
     *
     * Retrieved messages are made invisible to subsequent requests for the
     * duration of the visibility timeout. To permanently remove a message from
     * this queue, first retrieve the message and them delete it using the
     * {@link Services_Amazon_SQS_Queue::delete()} method.
     *
     * @param integer $count   optional. The number of messages to retrieve from
     *                         the queue. If not specified, one message is
     *                         retrieved. At most, ten messages may be
     *                         retrieved.
     * @param integer $timeout optional. The number of seconds that retrieved
     *                         messages should be hidden from view in the queue.
     *                         If not specified, the default visibility timeout
     *                         of this queue is used.
     *
     * @return array an array containing one or more retrieved messages. Each
     *               message in the returned array is represented as an
     *               associative array with the following keys:
     *               - <tt>id</tt>     - the message id.
     *               - <tt>body</tt>   - the body of the message.
     *               - <tt>handle</tt> - the receipt handle of the message.
     *
     * @throws Services_Amazon_SQS_InvalidTimeoutException if the provided
     *         <tt>$timeout</tt> is not in the valid range.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function receive($count = 1, $timeout = null)
    {
        if ($timeout !== null && !$this->isValidVisibilityTimeout($timeout)) {
            throw new Services_Amazon_SQS_InvalidTimeoutException('The ' .
                'specified timeout falls outside the allowable range (0-7200)',
                0, $timeout);
        }

        // normalize count if it's outside of Amazon's constraints
        $count = max($count, 1);
        $count = min($count, 10);

        $params = array();

        $params['Action']              = 'ReceiveMessage';
        $params['MaxNumberOfMessages'] = $count;

        if ($timeout) {
            $params['VisibilityTimeout'] = $timeout;
        }

        try {
            $response = $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            default:
                throw $e;
            }
        }

        // get messages from response
        $messages = array();
        $xpath    = $response->getXPath();
        $nodes    = $xpath->query('//sqs:Message');

        foreach ($nodes as $node) {
            $id     = $xpath->evaluate('string(sqs:MessageId/text())', $node);
            $handle = $xpath->evaluate('string(sqs:ReceiptHandle/text())', $node);
            $body   = $xpath->evaluate('string(sqs:Body/text())', $node);

            $message           = array();
            $message['id']     = $id;
            $message['body']   = $body;
            $message['handle'] = $handle;

            $messages[] = $message;
        }

        return $messages;
    }

    // }}}
    // {{{ delete()

    /**
     * Deletes a message from this queue
     *
     * @param string $handle the receipt handle of the message to delete.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function delete($handle)
    {
        $params = array();

        $params['Action']        = 'DeleteMessage';
        $params['ReceiptHandle'] = $handle;

        try {
            $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            default:
                throw $e;
            }
        }
    }

    // }}}
    // {{{ getAttributes()

    /**
     * Gets an associative array of one or more attributes of this queue
     *
     * Currently, Amazon SQS only allows retrieving the values of the
     * following attributes:
     *
     * - <tt>ApproximateNumberOfMessages</tt> - an approximation of the number
     *                                          of messages in this queue.
     * - <tt>VisibilityTimeout</tt>           - the default time period for
     *                                          which messages are made
     *                                          invisible when retrieved from
     *                                          this queue.
     *
     * Additionally, the special attribute <tt>All</tt> may be used to retrieve
     * all available attributes.
     *
     * @param string $name optional. The name of the attribute value to get or
     *                     <tt>All</tt> to get all available attributes. If
     *                     not specified, 'All' is used.
     *
     * @return array an associative array of available attributes. The attribute
     *               name is the array key and the attribute value is the
     *               array value.
     *
     * @throws Services_Amazon_SQS_InvalidAttributeException if an invalid
     *         attribute name is requested.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function getAttributes($name = 'All')
    {
        $params = array();

        $params['Action']        = 'GetQueueAttributes';
        $params['AttributeName'] = $name;

        try {
            $response = $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'InvalidAttributeName':
                throw new Services_Amazon_SQS_InvalidAttributeException('The ' .
                    'attribute name "' . $name . '" is not a valid attribute ' .
                    'name.', 0, $name);

            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            default:
                throw $e;
            }
        }

        $attributes = array();
        $xpath      = $response->getXPath();
        $nodes      = $xpath->query('//sqs:Attribute');

        foreach ($nodes as $node) {
            $name  = $xpath->evaluate('string(sqs:Name/text())', $node);
            $value = $xpath->evaluate('string(sqs:Value/text())', $node);

            $attributes[$name] = $value;
        }

        return $attributes;
    }

    // }}}
    // {{{ setAttribute()

    /**
     * Sets an attribute of this queue
     *
     * Currently, Amazon SQS only allows setting the <tt>VisibilityTimeout</tt>
     * attribute. This attribute sets the default time period for which
     * messages are made invisible when retrieved from this queue.
     *
     * @param string $name  the attribute name.
     * @param mixed  $value the attribute value.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_InvalidAttributeException if an invalid
     *         attribute name is set.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function setAttribute($name, $value)
    {
        if (!$this->isValidAttribute($name, $value)) {
            return false;
        }

        $params = array();

        $params['Action']          = 'SetQueueAttributes';
        $params['Attribute.Name']  = $name;
        $params['Attribute.Value'] = $value;

        try {
            $response = $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'InvalidAttributeName':
                throw new Services_Amazon_SQS_InvalidAttributeException('The ' .
                    'attribute name "' . $name . '" is not a valid attribute ' .
                    'name.', 0, $name);

            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            default:
                throw $e;
            }
        }
    }

    // }}}
}

?>
