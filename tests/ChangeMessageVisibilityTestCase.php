<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Change message visibility tests for the Services_Amazon_SQS package.
 *
 * These tests require the PHPUnit3 package to be installed. PHPUnit is
 * installable using PEAR. See the
 * {@link http://www.phpunit.de/pocket_guide/3.3/en/installation.html manual}
 * for detailed installation instructions.
 *
 * This test suite follows the PEAR AllTests conventions as documented at
 * {@link http://cvs.php.net/viewvc.cgi/pear/AllTests.php?view=markup}.
 *
 * LICENSE:
 *
 * Copyright 2009 silverorange
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
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * PHPUnit3 framework
 */
require_once 'PHPUnit/Framework.php';

/**
 * Services_Amazon_SQS test base class
 */
require_once dirname(__FILE__) . '/TestCase.php';

/**
 * Change message visibility tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_ChangeMessageVisibilityTestCase extends
    Services_Amazon_SQS_TestCase
{
    // {{{ testChangeMessageVisibility()

    /**
     * @group message
     */
    public function testChangeMessageVisibility()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ChangeMessageVisibilityResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>7eb71adb-362b-4ce4-a626-4dcd7ca0dfc2</RequestId>
  </ResponseMetadata>
</ChangeMessageVisibilityResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers);

        $this->queue->changeMessageVisibility(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0',
            3600
        );
    }

    // }}}
    // {{{ testChangeMessageVisibilityInvalidTimeout()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_InvalidTimeoutException
     */
    public function testChangeMessageVisibilityInvalidTimeout()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidParameterValue</Code>
    <Message>Value 86400 for parameter VisibilityTimeout is invalid. Reason: VisibilityTimeout must be an integer between 0 and 43200.</Message>
    <Detail/>
  </Error>
  <RequestId>7eb71adb-362b-4ce4-a626-4dcd7ca0dfc2</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $this->queue->changeMessageVisibility(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0',
            86400
        );
    }

    // }}}
    // {{{ testChangeMessageVisibilityInvalidMessage()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testChangeMessageVisibilityInvalidMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidParameterValue</Code>
    <Message>Value +eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qnn3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4PzHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0 for parameter ReceiptHandle is invalid. Reason: Message does not exist or is not avaliable for visibility timeout change..</Message><Detail/>
  </Error>
  <RequestId>1d9c2e47-933e-4896-ab93-86c4df071f7b</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $this->queue->changeMessageVisibility(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0',
            3600
        );
    }

    // }}}
    // {{{ testChangeMessageVisibilityWithInvalidQueue()

    /**
     * @group messages
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testChangeMessageVisibilityWithInvalidQueue()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>AWS.SimpleQueueService.NonExistentQueue</Code>
    <Message>The specified queue does not exist for this wsdl version.</Message>
    <Detail/>
  </Error>
  <RequestId>05714b4b-7359-4527-9bd1-c9aaacb4a2ad</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // Intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $queue = new Services_Amazon_SQS_Queue(
            'http://queue.amazonaws.com/this-queue-does-not-exist',
            '123456789ABCDEFGHIJK',
            'abcdefghijklmnopqrstuzwxyz/ABCDEFGHIJKLM',
            $this->request
        );

        $queue->changeMessageVisibility(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0',
            3600
        );
    }

    // }}}
    // {{{ testChangeMessageVisibilityWithUnknownError()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testChangeMessageVisibilityWithUnknownError()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Receiver</Type>
    <Code>InternalError</Code>
    <Message>We encountered an internal error. Please try again.</Message>
    <Detail/>
  </Error>
  <RequestId>d22acfe7-a4c3-4ab4-b0f3-3fbc20b97c1d</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // Intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse(
            $body,
            $headers,
            'HTTP/1.1 500 Internal Server Error'
        );

        $this->queue->changeMessageVisibility(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0',
            3600
        );
    }

    // }}}
}

?>
