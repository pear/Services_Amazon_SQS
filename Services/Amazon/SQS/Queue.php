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
 * @copyright 2008 Mike Brittain, 2008 Amazon.com, 2008-2009 silverorange
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
     * @param string                             $secretAccessKey if the second
     *        parameter is an account object, this parameter is ignored.
     *        Otherwise, this parameter is required and is the secret access
     *        key for the SQS account.
     *
     * @param HTTP_Request2                      $request         optional. The
     *        HTTP request object to use. If not specified, a HTTP request
     *        object is created automatically.
     */
    public function __construct($queueUrl, $accessKey, $secretAccessKey = '',
        HTTP_Request2 $request = null
    ) {
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
                throw new Services_Amazon_SQS_InvalidMessageException(
                    'The message contains characters outside the allowed set.',
                    0,
                    $message
                );
            case 'MessageTooLong':
                throw new Services_Amazon_SQS_InvalidMessageException(
                    'The message size can not exceed 8192 bytes.',
                    0,
                    $message
                );
            case 'InvalidParameterValue':
                $tooLongMessage = 'Value for parameter MessageBody is '
                    . 'invalid. Reason: Message body must be shorter than '
                    . '8192 bytes.';

                if ($e->getMessage() === $tooLongMessage) {
                    throw new Services_Amazon_SQS_InvalidMessageException(
                        'The message size can not exceed 8192 bytes.',
                        0,
                        $message
                    );
                }
                throw $e;
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException(
                    'The queue "' . $this . '" does not exist.',
                    0,
                    $this->_queueUrl
                );
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
     *               - <kbd>id</kbd>     - the message id.
     *               - <kbd>body</kbd>   - the body of the message.
     *               - <kbd>handle</kbd> - the receipt handle of the message.
     *
     * @throws Services_Amazon_SQS_InvalidTimeoutException if the provided
     *         <kbd>$timeout</kbd> is not in the valid range.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     *
     * @see Services_Amazon_SQS_Queue::changeMessageVisibility()
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
    // {{{ changeMessageVisibility()

    /**
     * Changes the visibility timeout for a message in this queue
     *
     * Once a message is received, it is invisible to the queue for the
     * duration of the visibility timeout. After receiving the message, the
     * visibility timeout may be increased if the queue operation will take
     * longer than the default visibility timeout.
     *
     * Message visibility may be changed multiple times, but a single received
     * message can not have a total visibility timeout period exceeding 12
     * hours.
     *
     * Example usage:
     * <code>
     * <?php
     * // receive a message with a 10 second visibility timeout
     * $message = $queue->receive(1, 10);
     * // check if processing the message will take longer than ten seconds
     * if ($message['body'] == 'this will take a long time') {
     *    // if so, add five minutes to the visibility timeout
     *    $queue->changeMessageVisibility($message['handle'], 300);
     * }
     * // now process the message
     * ?>
     * </code>
     *
     * @param string  $handle  the receipt handle of the message to update.
     * @param integer $timeout the new visibility timeout for the message.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_InvalidTimeoutException if the provided
     *         <kbd>$timeout</kbd> is not in the valid range for the given
     *         message.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     */
    public function changeMessageVisibility($handle, $timeout)
    {
        $params = array();

        $params['Action']            = 'ChangeMessageVisibility';
        $params['ReceiptHandle']     = $handle;
        $params['VisibilityTimeout'] = $timeout;

        try {
            $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            case 'InvalidParameterValue':
                $exp = '/^Value .*? for parameter VisibilityTimeout is ' .
                    'invalid\. Reason: VisibilityTimeout must be an integer ' .
                    'between 0 and 43200\.$/';

                if (preg_match($exp, $e->getMessage()) === 1) {
                   throw new Services_Amazon_SQS_InvalidTimeoutException(
                        'The timeout "' . $timeout . '" is not valid for ' .
                        'the specified message.',
                        0,
                        $timeout
                    );
                }

                throw $e;

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
     * - <kbd>ApproximateNumberOfMessages</kbd> - an approximation of the number
     * - <kbd>CreatedTimestamp</kbd>            - the date this queue was
     *                                            created.
     * - <kbd>LastModifiedTimestamp</kbd>       - the date this queue was
     *                                            last modified.
     * - <kbd>VisibilityTimeout</kbd>           - the default time period for
     *                                            which messages are made
     *                                            invisible when retrieved from
     *                                            this queue.
     *
     * Timestamp values are formatted as Unix timestamps.
     *
     * Additionally, the special attribute <kbd>All</kbd> may be used to
     * retrieve all available attributes.
     *
     * @param string|array $name optional. The name or names of the attribute
     *                           values to get or <kbd>All</kbd> to get all
     *                           available attributes. If not specified,
     *                           <i><kbd>All</kdb></i> is used. Multiple
     *                           specific attributes may be retrieved using an
     *                           array of attribute names.
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
        $params = array('Action' => 'GetQueueAttributes');

        if (!is_array($name)) {
            $name = array($name);
        }

        $count = count($name);

        if ($count === 1) {
            $params['AttributeName'] = reset($name);
        } elseif ($count > 1) {
            $count = 1;
            foreach ($name as $attributeName) {
                $params['AttributeName.' . $count] = $attributeName;
                $count++;
            }
        }

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
     * Currently, Amazon SQS only allows setting the
     * <kbd>VisibilityTimeout</kbd> attribute. This attribute sets the default
     * time period for which messages are made invisible when retrieved from
     * this queue.
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
    // {{{ addPermission()

    /**
     * Adds a permission to this queue for a specific principal
     *
     * Permissions that may be granted on this queue are:
     *
     * - <kbd>*</kbd> (all permissions)
     * - <kbd>SendMessage</kbd>
     * - <kbd>ReceiveMessage</kbd>
     * - <kbd>DeleteMessage</kbd>
     * - <kbd>ChangeMessageVisibility</kbd>
     * - <kbd>GetQueueAttributes</kbd>
     *
     * Example use:
     * <code>
     * <?php
     * $queue->addPermission(
     *     'billing-read-only',
     *     array(
     *         array(
     *             'account'   => '123456789012',
     *             'permission => 'ReceiveMessage'
     *         ),
     *         array(
     *             'account'   => '123456789012',
     *             'permission => 'GetQueueAttributes'
     *         ),
     *     )
     * );
     * ?>
     * </code>
     *
     * @param string $label      a unique identifier for this permission. This
     *                           label can be used to revoke the permission at
     *                           a later date.
     * @param array  $principals an array of principals receiving the
     *                           permission. Each array element is a
     *                           separate array containing the following keys:
     *                           - <kbd>account</kbd>    - the id of the AWS
     *                                                     account which will
     *                                                     receive the
     *                                                     permission. This is
     *                                                     <em>not</em> an
     *                                                     AWS key id.
     *                           - <kbd>permission</kbd> - the permission to
     *                                                     grant.
     *
     * @return void
     *
     * @throws InvalidArgumentException if no principals are specified, or if
     *         the specified principals do not contain the <kbd>account</kbd>
     *         and <kbd>permission</kbd> fields.
     *
     * @throws Services_Amazon_SQS_InvalidPermissionLabelException if the
     *         specified permission label is not valid.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     *
     * @see Services_Amazon_SQS_Queue::removePermission()
     */
    public function addPermission($label, array $principals)
    {
        if (!$this->isValidPermissionLabel($label)) {
            throw new Services_Amazon_SQS_InvalidPermissionLabelException(
                'The permission label "' . $label . '" is not a valid ' .
                'permission label. Permission labels must be 1-80 ' .
                'characters long and must consist only of alphanumeric ' .
                'characters, dashes or underscores.',
                0,
                $label
            );
        }

        // validate principals
        if (count($principals) === 0) {
            throw new InvalidArgumentException(
                'At least one principal must be specified.'
            );
        }
        foreach ($principals as $principal) {
            if (   !is_array($principal)
                || !array_key_exists('account', $principal)
                || !array_key_exists('permission', $principal)
            ) {
                throw new InvalidArgumentException(
                    'Each principal must be specified as an associative ' .
                    'array with the following keys: "account", and ' .
                    '"permission".'
                );
            }
        }

        $params = array();

        $params['Action'] = 'AddPermission';
        $params['Label']  = $label;

        if (count($principals) === 1) {
            $principal = reset($principals);
            $params['AWSAccountId'] = $principal['account'];
            $params['ActionName']   = $principal['permission'];
        } else {
            $count = 1;
            foreach ($principals as $principal) {
                $params['AWSAccountId.' . $count] = $principal['account'];
                $params['ActionName.' . $count]   = $principal['permission'];
                $count++;
            }
        }

        try {
            $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            case 'InvalidParameterValue':
                $exp = '/^Value .*? for parameter Label is invalid\. ' .
                    'Reason: Already exists\.\.$/';

                if (preg_match($exp, $e->getMessage()) === 1) {
                    throw new Services_Amazon_SQS_InvalidPermissionLabelException(
                        'Permission label "' . $label . '" is already used ' .
                        'for another permission. A different label must be ' .
                        'used for this permission.',
                        0,
                        $label
                    );
                }

                throw $e;

            default:
                throw $e;
            }
        }
    }

    // }}}
    // {{{ removePermission()

    /**
     * Removes a permission from this queue by the permission's label
     *
     * Example use:
     * <code>
     * <?php
     * $queue->removePermission('billing-read-only');
     * ?>
     * </code>
     *
     * @param string $label the unique identifier of the permission to remove.
     *                      This should be the same label used when the
     *                      permission was added.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_InvalidPermissionLabelException if the
     *         specified permission label is not valid.
     *
     * @throws Services_Amazon_SQS_InvalidQueueException if this queue does
     *         not exist for the Amazon SQS account.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned by Amazon.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     *
     * @see Services_Amazon_SQS_Queue::addPermission()
     */
    public function removePermission($label)
    {
        if (!$this->isValidPermissionLabel($label)) {
            throw new Services_Amazon_SQS_InvalidPermissionLabelException(
                'The permission label "' . $label . '" is not a valid ' .
                'permission label. Permission labels must be 1-80 ' .
                'characters long and must consist only of alphanumeric ' .
                'characters, dashes or underscores.',
                0,
                $label
            );
        }

        $params = array();

        $params['Action'] = 'RemovePermission';
        $params['Label']  = $label;

        try {
            $this->sendRequest($params, $this->_queueUrl);
        } catch (Services_Amazon_SQS_ErrorException $e) {
            switch ($e->getError()) {
            case 'AWS.SimpleQueueService.NonExistentQueue':
                throw new Services_Amazon_SQS_InvalidQueueException('The ' .
                    'queue "' . $this . '" does not exist.', 0,
                    $this->_queueUrl);

            case 'InvalidParameterValue':
                $exp = '/^Value .*? for parameter Label is invalid\. ' .
                    'Reason: Does not exist\.\.$/';

                if (preg_match($exp, $e->getMessage()) === 1) {
                    throw new Services_Amazon_SQS_InvalidPermissionLabelException(
                        'Permission label "' . $label . '" does not exist ' .
                        'for this queue and cannot be removed.',
                        0,
                        $label
                    );
                }

                throw $e;

            default:
                throw $e;
            }
        }
    }

    // }}}
}

?>
