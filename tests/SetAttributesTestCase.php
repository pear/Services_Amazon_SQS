<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Set attributes tests for the Services_Amazon_SQS package.
 *
 * These tests require the PHPUnit 3.6 or greater package to be installed.
 * PHPUnit is installable using PEAR. See the
 * {@link http://www.phpunit.de/manual/3.6/en/installation.html manual}
 * for detailed installation instructions.
 *
 * This test suite follows the PEAR AllTests conventions as documented at
 * {@link http://cvs.php.net/viewvc.cgi/pear/AllTests.php?view=markup}.
 *
 * LICENSE:
 *
 * Copyright 2008 Mike Brittain, 2008-2011 silverorange
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
 * @copyright 2008 Mike Brittain
 * @copyright 2008-2011 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * Services_Amazon_SQS test base class
 */
require_once dirname(__FILE__) . '/TestCase.php';

/**
 * Set attributes tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain
 * @copyright 2008-2011 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_SetAttributesTestCase extends
    Services_Amazon_SQS_TestCase
{
    // {{{ testSetAttribute()

    /**
     * @group attributes
     */
    public function testSetAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<SetQueueAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>ede893f6-d22b-4eb8-8636-a325a4407ce8</RequestId>
  </ResponseMetadata>
</SetQueueAttributesResponse>
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
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $this->queue->setAttribute('VisibilityTimeout', 123);
    }

    // }}}
    // {{{ testSetInvalidAttribute()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_InvalidAttributeException
     */
    public function testSetInvalidAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidAttributeName</Code>
    <Message>Unknown Attribute InvalidAttributeName</Message>
    <Detail/>
  </Error>
  <RequestId>0dfad892-bcd7-481b-8954-ed6a69245b00</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $response = $this->queue->setAttribute('InvalidAttributeName', 1);
    }

    // }}}
    // {{{ testSetInvalidAttributeValue()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testSetInvalidAttributeValue()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidAttributeValue</Code>
    <Message>Attribute VisibilityTimeout must be an integer between 0 and 7200</Message>
    <Detail/>
  </Error>
  <RequestId>cc46c162-d65f-4874-bf74-79d28e00b181</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $this->queue->setAttribute('VisibilityTimeout', 10000);
    }

    // }}}
    // {{{ testSetAttributeWithInvalidQueue()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testSetAttributeWithInvalidQueue()
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

        $queue->setAttribute('VisibilityTimeout', 10000);
    }

    // }}}
    // {{{ testSetAttributeWithUnknownError()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testSetAttributeWithUnknownError()
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

        $this->queue->setMaximumRetries(1);

        $this->queue->setAttribute('VisibilityTimeout', 10000);
    }

    // }}}
}

?>
