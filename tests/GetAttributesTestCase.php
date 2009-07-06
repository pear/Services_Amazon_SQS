<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Get attributes tests for the Services_Amazon_SQS package.
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
 * Copyright 2008 Mike Brittain, 2008-2009 silverorange
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
 * @copyright 2008-2009 silverorange
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
 * Get attributes tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain
 * @copyright 2008-2009 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_GetAttributesTestCase extends
    Services_Amazon_SQS_TestCase
{
    // {{{ testGetAllAttributes()

    /**
     * @group attributes
     */
    public function testGetAllAttributes()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>VisibilityTimeout</Name>
      <Value>123</Value>
    </Attribute>
    <Attribute>
      <Name>ApproximateNumberOfMessages</Name>
      <Value>456</Value>
    </Attribute>
    <Attribute>
      <Name>CreatedTimestamp</Name>
      <Value>1245542635</Value>
    </Attribute>
    <Attribute>
      <Name>LastModifiedTimestamp</Name>
      <Value>1245542835</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes();

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(4, count($attributes));

        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertEquals(123, $attributes['VisibilityTimeout']);

        $this->assertArrayHasKey('ApproximateNumberOfMessages', $attributes);
        $this->assertEquals(456, $attributes['ApproximateNumberOfMessages']);

        $this->assertArrayHasKey('CreatedTimestamp', $attributes);
        $this->assertEquals(1245542635, $attributes['CreatedTimestamp']);

        $this->assertArrayHasKey('LastModifiedTimestamp', $attributes);
        $this->assertEquals(1245542835, $attributes['LastModifiedTimestamp']);
    }

    // }}}
    // {{{ testGetMultiAttributes()

    /**
     * @group attributes
     */
    public function testGetMultiAttributes()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>CreatedTimestamp</Name>
      <Value>1245542635</Value>
    </Attribute>
    <Attribute>
      <Name>LastModifiedTimestamp</Name>
      <Value>1245542835</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes(
            array(
                'CreatedTimestamp',
                'LastModifiedTimestamp'
            )
        );

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(2, count($attributes));

        $this->assertArrayHasKey('CreatedTimestamp', $attributes);
        $this->assertEquals(1245542635, $attributes['CreatedTimestamp']);

        $this->assertArrayHasKey('LastModifiedTimestamp', $attributes);
        $this->assertEquals(1245542835, $attributes['LastModifiedTimestamp']);
    }

    // }}}
    // {{{ testGetEmptyAttributes()

    /**
     * @group attributes
     */
    public function testGetEmptyAttributes()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes(array());

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(0, count($attributes));
    }

    // }}}
    // {{{ testGetVisibilityTimeoutAttribute()

    /**
     * @group attributes
     */
    public function testGetVisibilityTimeoutAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>VisibilityTimeout</Name>
      <Value>123</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes('VisibilityTimeout');

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(1, count($attributes));

        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertEquals(123, $attributes['VisibilityTimeout']);
    }

    // }}}
    // {{{ testGetApproximateNumberOfMessagesAttribute()

    /**
     * @group attributes
     */
    public function testGetApproximateNumberOfMessagesAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>ApproximateNumberOfMessages</Name>
      <Value>123</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes('ApproximateNumberOfMessages');

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(1, count($attributes));

        $this->assertArrayHasKey('ApproximateNumberOfMessages', $attributes);
        $this->assertEquals(123, $attributes['ApproximateNumberOfMessages']);
    }

    // }}}
    // {{{ testGetCreatedTimestampAttribute()

    /**
     * @group attributes
     */
    public function testGetCreatedTimestampAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>CreatedTimestamp</Name>
      <Value>1245542635</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes('CreatedTimestamp');

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(1, count($attributes));

        $this->assertArrayHasKey('CreatedTimestamp', $attributes);
        $this->assertEquals(1245542635, $attributes['CreatedTimestamp']);
    }

    // }}}
    // {{{ testGetLastModifiedTimestampAttribute()

    /**
     * @group attributes
     */
    public function testGetLastModifiedTimestampAttribute()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<GetAttributesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <GetAttributesResult>
    <Attribute>
      <Name>LastModifiedTimestamp</Name>
      <Value>1245542635</Value>
    </Attribute>
  </GetAttributesResult>
  <ResponseMetadata>
    <RequestId>ac8e1fc5-4fe7-499c-b2ea-a3c183dda6aa</RequestId>
  </ResponseMetadata>
</GetAttributesResponse>
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

        $attributes = $this->queue->getAttributes('LastModifiedTimestamp');

        $this->assertTrue(
            is_array($attributes),
            'Returned attributes value is not an array.'
        );

        $this->assertEquals(1, count($attributes));

        $this->assertArrayHasKey('LastModifiedTimestamp', $attributes);
        $this->assertEquals(1245542635, $attributes['LastModifiedTimestamp']);
    }

    // }}}
    // {{{ testGetInvalidAttribute()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_InvalidAttributeException
     */
    public function testGetInvalidAttribute()
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

        $response = $this->queue->getAttributes('InvalidAttributeName');
    }

    // }}}
    // {{{ testGetMultiInvalidAttribute()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_InvalidAttributeException
     */
    public function testGetMultiInvalidAttribute()
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

        $response = $this->queue->getAttributes(
            array(
                'CreatedTimestamp',
                'InvalidAttributeName'
            )
        );
    }

    // }}}
    // {{{ testGetAttributesWithInvalidQueue()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testGetAttributesWithInvalidQueue()
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

        $attributes = $queue->getAttributes();
    }

    // }}}
    // {{{ testGetAttributesWithUnknownError()

    /**
     * @group attributes
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testGetAttributesWithUnknownError()
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

        $attributes = $this->queue->getAttributes();
    }

    // }}}
}

?>
