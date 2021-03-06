<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Add permission tests for the Services_Amazon_SQS package.
 *
 * These tests require the PHPUnit 3.6 or greater package to be installed.
 * PHPUnit is installable using PEAR. See the
 * {@link http://www.phpunit.de/manual/3.6/en/installation.html manual}
 * for detailed installation instructions.
 *
 * This test suite follows the PEAR AllTests conventions as documented at
 * {@link http://svn.php.net/viewvc/pear/packages/AllTests.php?view=markup}.
 *
 * LICENSE:
 *
 * Copyright 2009-2011 silverorange
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
 * @copyright 2009-2011 silverorange
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
 * Add permission tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009-2011 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_AddPermissionTestCase extends
    Services_Amazon_SQS_TestCase
{
    // {{{ testAddPermissionWithMultiplePrincipals()

    /**
     * @group permissions
     */
    public function testAddPermissionWithMultiplePrincipals()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<AddPermissionResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>6b277d5b-8ab6-490e-a3e9-51e72ec5e5b2</RequestId>
  </ResponseMetadata>
</AddPermissionResponse>
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

        $this->queue->addPermission(
            'read-only',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'ReceiveMessage'
                ),
                array(
                    'account'    => '123456789012',
                    'permission' => 'GetQueueAttributes'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithSinglePrincipal()

    /**
     * @group permissions
     */
    public function testAddPermissionWithSinglePrincipal()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<AddPermissionResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>6b277d5b-8ab6-490e-a3e9-51e72ec5e5b2</RequestId>
  </ResponseMetadata>
</AddPermissionResponse>
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

        $this->queue->addPermission(
            'read-only',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'ReceiveMessage'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithNoPrincipals()

    /**
     * @group permissions
     * @expectedException InvalidArgumentException
     */
    public function testAddPermissionWithNoPrincipals()
    {
        $this->queue->addPermission('read-only', array());
    }

    // }}}
    // {{{ testAddPermissionWithInvalidPrincipal()

    /**
     * @group permissions
     * @expectedException InvalidArgumentException
     */
    public function testAddPermissionWithInvalidPrincipal()
    {
        $this->queue->addPermission(
            'read-only',
            array(
                array(
                    'foo' => 'bar'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithDuplicateLabel()

    /**
     * @group permissions
     * @expectedException Services_Amazon_SQS_InvalidPermissionLabelException
     */
    public function testAddPermissionWithDuplicateLabel()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidParameterValue</Code>
    <Message>Value read-only for parameter Label is invalid. Reason: Already exists..</Message>
    <Detail/>
  </Error>
  <RequestId>809d09df-7ba0-4344-8343-df6611fab1fd</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $this->queue->addPermission(
            'read-only',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'ReceiveMessage'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithInvalidQueue()

    /**
     * @group permissions
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testAddPermissionWithInvalidQueue()
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

        $queue->addPermission(
            'test-label',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'SendMessage'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithInvalidPermissionLabel()

    /**
     * @group permissions
     * @expectedException Services_Amazon_SQS_InvalidPermissionLabelException
     */
    public function testAddPermissionWithInvalidPermissionLabel()
    {
        $this->queue->addPermission(
            '$foo',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'SendMessage'
                )
            )
        );
    }

    // }}}
    // {{{ testAddPermissionWithUnknownError()

    /**
     * @group permissions
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testAddPermissionWithUnknownError()
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

        $this->queue->addPermission(
            'test-label',
            array(
                array(
                    'account'    => '123456789012',
                    'permission' => 'ReceiveMessage'
                )
            )
        );
    }

    // }}}
}

?>
