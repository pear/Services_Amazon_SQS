<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Tests for the Services_Amazon_SQS package.
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
 * Queue manager class to test
 */
require_once 'Services/Amazon/SQS/QueueManager.php';

/**
 * For mock HTTP responses
 *
 * @see http://clockwerx.blogspot.com/2008/11/pear-and-unit-tests-httprequest2.html
 */
require_once 'HTTP/Request2.php';

/**
 * For mock HTTP responses
 *
 * @see http://clockwerx.blogspot.com/2008/11/pear-and-unit-tests-httprequest2.html
 */
require_once 'HTTP/Request2/Adapter/Mock.php';

/**
 * Tests for Services_Amazon_SQS
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
class Services_Amazon_SQS_TestCase extends PHPUnit_Framework_TestCase
{
    // {{{ protected properties

    /**
     * @var HTTP_Request2_Adapter_Mock
     *
     * @see Services_Akismet2_TestCase::addHttpResponse()
     */
    protected $mock = null;

    /**
     * @var Services_Amazon_SQS_QueueManager
     */
    protected $manager = null;

    /**
     * @var Services_Amazon_SQS_Queue
     */
    protected $queue = null;

    // }}}
    // {{{ private properties

    /**
     * @var integer
     */
    private $_oldErrorLevel = 0;

    // }}}
    // {{{ private properties

    /**
     * The Amazon Web Services access key id
     *
     * Value is set during {@link Services_Amazon_SQS_TestCase::setUp()}.
     *
     * @var string
     */
    protected $accessKey = '';

    /**
     * The Amazon Web Services secret access key
     *
     * Value is set during {@link Services_Amazon_SQS_TestCase::setUp()}.
     *
     * @var string
     */
    protected $secretAccessKey = '';

    // }}}
    // {{{ setUp()

    public function setUp()
    {
        $this->_oldErrorLevel = error_reporting(E_ALL | E_STRICT);

        $this->mock = new HTTP_Request2_Adapter_Mock();

        $request = new HTTP_Request2();
        $request->setAdapter($this->mock);

        $this->manager = new Services_Amazon_SQS_QueueManager(
            '123456789ABCDEFGHIJK',
            'abcdefghijklmnopqrstuzwxyz/ABCDEFGHIJKLM',
            $request
        );

        $this->queue = new Services_Amazon_SQS_Queue(
            'http://queue.amazonaws.com/example',
            '123456789ABCDEFGHIJK',
            'abcdefghijklmnopqrstuzwxyz/ABCDEFGHIJKLM',
            $request
        );
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        unset($this->manager);
        unset($this->queue);
        unset($this->mock);

        // restore error handling
        error_reporting($this->_oldErrorLevel);
    }

    // }}}
    // {{{ addHttpResponse()

    protected function addHttpResponse($body, $headers = array(),
        $status = 'HTTP/1.1 200 OK'
    ) {
        $response = new HTTP_Request2_Response($status);
        foreach ($headers as $name => $value) {
            $headerLine = $name . ': ' . $value;
            $response->parseHeaderLine($headerLine);
        }
        $response->appendBody($body);
        $this->mock->addResponse($response);
    }

    // }}}
    // {{{ formatXml()

    protected function formatXml($xml)
    {
        $xml = preg_replace('/^ +/m', '', $xml);
        $xml = preg_replace('/([^\?])>\n/s', '\1>', $xml);
        return $xml;
    }

    // }}}

    // queue tests
    // {{{ testDeleteQueue()

    /**
     * @group queue
     */
    public function testDeleteQueue()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<DeleteQueueResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>4a5a1753-d3f7-478b-aef8-3be0d1c8fe73</RequestId>
  </ResponseMetadata>
</DeleteQueueResponse>
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

        $queues = $this->manager->deleteQueue($this->queue);
    }

    // }}}
    // {{{ testDeleteQueueByQueueUrl()

    /**
     * @group queue
     */
    public function testDeleteQueueByQueueUrl()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<DeleteQueueResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>4a5a1753-d3f7-478b-aef8-3be0d1c8fe73</RequestId>
  </ResponseMetadata>
</DeleteQueueResponse>
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

        $queues = $this->manager->deleteQueue(
            'http://queue.amazonaws.com/example'
        );
    }

    // }}}
    // {{{ testListQueues()

    /**
     * @group queue
     */
    public function testListQueues()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ListQueuesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ListQueuesResult>
    <QueueUrl>http://queue.amazonaws.com/this-is-a-test</QueueUrl>
    <QueueUrl>http://queue.amazonaws.com/test-queue</QueueUrl>
    <QueueUrl>http://queue.amazonaws.com/example-queue</QueueUrl>
    <QueueUrl>http://queue.amazonaws.com/examples</QueueUrl>
  </ListQueuesResult>
  <ResponseMetadata>
    <RequestId>67e6d0ad-f788-4ed4-9a40-ad184177f557</RequestId>
  </ResponseMetadata>
</ListQueuesResponse>
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

        $queues = $this->manager->listQueues();
        $this->assertTrue(is_array($queues));
        $this->assertEquals(4, count($queues));

        $expectedQueueUrls = array(
            'http://queue.amazonaws.com/this-is-a-test',
            'http://queue.amazonaws.com/test-queue',
            'http://queue.amazonaws.com/example-queue',
            'http://queue.amazonaws.com/examples'
        );

        foreach ($queues as $key => $queue) {
            $this->assertType('Services_Amazon_SQS_Queue', $queue);
            $this->assertEquals($expectedQueueUrls[$key], strval($queue));
        }
    }

    // }}}
    // {{{ testListQueuesWithPrefix()

    /**
     * @group queue
     */
    public function testListQueuesWithPrefix()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ListQueuesResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ListQueuesResult>
    <QueueUrl>http://queue.amazonaws.com/example-queue</QueueUrl>
    <QueueUrl>http://queue.amazonaws.com/examples</QueueUrl>
  </ListQueuesResult>
  <ResponseMetadata>
    <RequestId>67e6d0ad-f788-4ed4-9a40-ad184177f557</RequestId>
  </ResponseMetadata>
</ListQueuesResponse>
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

        $queues = $this->manager->listQueues('example');
        $this->assertTrue(is_array($queues));
        $this->assertEquals(2, count($queues));

        $expectedQueueUrls = array(
            'http://queue.amazonaws.com/example-queue',
            'http://queue.amazonaws.com/examples'
        );

        foreach ($queues as $key => $queue) {
            $this->assertType('Services_Amazon_SQS_Queue', $queue);
            $this->assertEquals($expectedQueueUrls[$key], strval($queue));
        }
    }

    // }}}
    // {{{ testCreateQueue()

    /**
     * @group queue
     */
    public function testCreateQueue()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<CreateQueueResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <CreateQueueResult>
    <QueueUrl>http://queue.amazonaws.com/example</QueueUrl>
  </CreateQueueResult>
  <ResponseMetadata>
    <RequestId>9a3bdd9b-34e4-48c7-b071-9edfc1500b88</RequestId>
  </ResponseMetadata>
</CreateQueueResponse>
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

        $queue = $this->manager->createQueue('example');

        $this->assertType('Services_Amazon_SQS_Queue', $queue);
        $this->assertEquals(
            'http://queue.amazonaws.com/example',
            strval($queue)
        );
    }

    // }}}
    // {{{ testCreateQueueWithVisibilityTimeout()

    /**
     * @group queue
     */
    public function testCreateQueueWithVisibilityTimeout()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<CreateQueueResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <CreateQueueResult>
    <QueueUrl>http://queue.amazonaws.com/example</QueueUrl>
  </CreateQueueResult>
  <ResponseMetadata>
    <RequestId>9a3bdd9b-34e4-48c7-b071-9edfc1500b88</RequestId>
  </ResponseMetadata>
</CreateQueueResponse>
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

        $queue = $this->manager->createQueue('example', 500);

        $this->assertType('Services_Amazon_SQS_Queue', $queue);
        $this->assertEquals(
            'http://queue.amazonaws.com/example',
            strval($queue)
        );
    }

    // }}}
    // {{{ testCreateQueueWithInvalidVisibilityTimeout()

    /**
     * @group queue
     * @expectedException Services_Amazon_SQS_InvalidTimeoutException
     */
    public function testCreateQueueWithInvalidVisibilityTimeout()
    {
        $queue = $this->manager->createQueue('example', 10000);
    }

    // }}}

    // attribute tests
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

        $this->assertEquals(2, count($attributes));

        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertEquals(123, $attributes['VisibilityTimeout']);

        $this->assertArrayHasKey('ApproximateNumberOfMessages', $attributes);
        $this->assertEquals(456, $attributes['ApproximateNumberOfMessages']);
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

    // message tests
    // {{{ testSendMessage()

    /**
     * @group message
     */
    public function testSendMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<SendMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <SendMessageResult>
    <MD5OfMessageBody>8b25734299a8efa7eeb74bf261bdc72d</MD5OfMessageBody>
    <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
  </SendMessageResult>
  <ResponseMetadata>
    <RequestId>ead9e7a4-10c2-4c59-8a4e-4381bab50565</RequestId>
  </ResponseMetadata>
</SendMessageResponse>
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

        $messageId = $this->queue->send('Services_Amazon_SQS Unit Test');

        $this->assertEquals('90b160de-132b-45c6-afea-4679b27a485d', $messageId);
    }

    // }}}
    // {{{ testReceiveOneMessage()

    /**
     * @group message
     */
    public function testReceiveOneMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ReceiveMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ReceiveMessageResult>
    <Message>
      <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qnn3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4PzHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0=</ReceiptHandle>
      <MD5OfBody>8b25734299a8efa7eeb74bf261bdc72d</MD5OfBody>
      <Body>Services_Amazon_SQS Unit Test</Body>
    </Message>
  </ReceiveMessageResult>
  <ResponseMetadata>
    <RequestId>c890aaba-51fc-4ab4-85d2-a935ea181f60</RequestId>
  </ResponseMetadata>
</ReceiveMessageResponse>
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

        $messages = $this->queue->receive();

        $this->assertEquals(1, count($messages));

        $this->assertArrayHasKey(
            0,
            $messages,
            'Messages are not numerically indexed from 0.'
        );

        $message = $messages[0];

        $this->assertTrue(is_array($message), 'Message is not an array.');

        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('body', $message);
        $this->assertArrayHasKey('handle', $message);
    }

    // }}}
    // {{{ testReceiveManyMessages()

    /**
     * @group message
     */
    public function testReceiveManyMessages()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ReceiveMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ReceiveMessageResult>
    <Message>
      <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qnn3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4PzHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0=</ReceiptHandle>
      <MD5OfBody>8b25734299a8efa7eeb74bf261bdc72d</MD5OfBody>
      <Body>the</Body>
    </Message>
    <Message>
      <MessageId>f5ce8da6-8874-4608-83fa-e78f30a1cd1f</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQHvhUF2kJ7IQ8o/410f2K/YWoEImH75JlwWnAfQIqV4L2WWUq+cLOUQvOPmlQASPVpLlYuLaau4pCK+yTvqXHpkB6lkPzc0V/4djNn8TlYsW1suYBw9LkHssbAFuUkow5NgPuBDvw8V7UTn6hHkUROo=</ReceiptHandle>
      <MD5OfBody>acbd18db4cc2f85cedef654fccc4a4d8</MD5OfBody>
      <Body>quick</Body>
    </Message>
    <Message>
      <MessageId>c913c694-c5f3-4637-be83-6506623c807c</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQAqTwmqKi9DuwlcpshHNCZQTwolrsonDv+DxH9ur7SBaLaXZCJ8LoQnX5fdVFQUb8rRQNWJqrMXrUcc3S9ZD/moiAPI2KT9euTubylT9wrYEzrXp0EMvE9Rx0eg5pWzC9uWXyMEpMjbWtFgLo2wcVEs=</ReceiptHandle>
      <MD5OfBody>1df3746a4728276afdc24f828186f73a</MD5OfBody>
      <Body>brown</Body>
    </Message>
    <Message>
      <MessageId>898cb1ab-c74e-4a0f-b597-1433ce03f056</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQHpEi8vjbiIXepp8vWhPeCAZc3GpfGJRGqEbWsfMKVbTupbrl6s47gfDAA1Ww6R4FM6SdKYE993x2GAX70hR4T2WYKwCmA3N3hVQIyXqu3aUi2+wORPqZKLBM14y6hUwgIA9J7O2dPbCUTn6hHkUROo=</ReceiptHandle>
      <MD5OfBody>2b95d1f09b8b66c5c43622a4d9ec9a04</MD5OfBody>
      <Body>fox</Body>
    </Message>
    <Message>
      <MessageId>51245ccb-0e97-4cdd-8e99-a3ed3f6e5ac7</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQH6genZB61KXsXdn2v3Lwq8FsRf7Nw+f11JrUoe9kZPiiMdlJGLSsPaClgoLKONKlkRU+qGXK4i4+aV5+65fs3laSVvREzzrh9WZvhs0BoiLbuYByTHaoYljBbctKRQVIoVmk+FpM7/e9bnPhfj2gbM=</ReceiptHandle>
      <MD5OfBody>0ffe34b4e04c2b282c5a388b1ad8aa7a</MD5OfBody>
      <Body>jumps</Body>
    </Message>
  </ReceiveMessageResult>
  <ResponseMetadata>
    <RequestId>3022b953-0e76-48d7-a4f1-b351d6e1dbb1</RequestId>
  </ResponseMetadata>
</ReceiveMessageResponse>
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

        $messages = $this->queue->receive();

        // {{{ expected messages
        $expectedMessages = array(
            array(
                'id'     => '90b160de-132b-45c6-afea-4679b27a485d',
                'body'   => 'the',
                'handle' => '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
                            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
                            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
                            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0='
            ),
            array(
                'id'     => 'f5ce8da6-8874-4608-83fa-e78f30a1cd1f',
                'body'   => 'quick',
                'handle' => '+eXJYhj5rDqRunVNVvjOQHvhUF2kJ7IQ8o/410f2K/Y' .
                            'WoEImH75JlwWnAfQIqV4L2WWUq+cLOUQvOPmlQASPVp' .
                            'LlYuLaau4pCK+yTvqXHpkB6lkPzc0V/4djNn8TlYsW1' .
                            'suYBw9LkHssbAFuUkow5NgPuBDvw8V7UTn6hHkUROo='
            ),
            array(
                'id'     => 'c913c694-c5f3-4637-be83-6506623c807c',
                'body'   => 'brown',
                'handle' => '+eXJYhj5rDqRunVNVvjOQAqTwmqKi9DuwlcpshHNCZQ' .
                            'TwolrsonDv+DxH9ur7SBaLaXZCJ8LoQnX5fdVFQUb8r' .
                            'RQNWJqrMXrUcc3S9ZD/moiAPI2KT9euTubylT9wrYEz' .
                            'rXp0EMvE9Rx0eg5pWzC9uWXyMEpMjbWtFgLo2wcVEs='
            ),
            array(
                'id'     => '898cb1ab-c74e-4a0f-b597-1433ce03f056',
                'body'   => 'fox',
                'handle' => '+eXJYhj5rDqRunVNVvjOQHpEi8vjbiIXepp8vWhPeCA' .
                            'Zc3GpfGJRGqEbWsfMKVbTupbrl6s47gfDAA1Ww6R4FM' .
                            '6SdKYE993x2GAX70hR4T2WYKwCmA3N3hVQIyXqu3aUi' .
                            '2+wORPqZKLBM14y6hUwgIA9J7O2dPbCUTn6hHkUROo='
            ),
            array(
                'id'     => '51245ccb-0e97-4cdd-8e99-a3ed3f6e5ac7',
                'body'   => 'jumps',
                'handle' => '+eXJYhj5rDqRunVNVvjOQH6genZB61KXsXdn2v3Lwq8' .
                            'FsRf7Nw+f11JrUoe9kZPiiMdlJGLSsPaClgoLKONKlk' .
                            'RU+qGXK4i4+aV5+65fs3laSVvREzzrh9WZvhs0BoiLb' .
                            'uYByTHaoYljBbctKRQVIoVmk+FpM7/e9bnPhfj2gbM='
            ),
        );
        // }}}
        $this->assertEquals($expectedMessages, $messages);
    }

    // }}}
    // {{{ testReceiveWithVisibilityTimeout()

    /**
     * @group message
     */
    public function testReceiveWithVisibilityTimeout()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ReceiveMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ReceiveMessageResult>
    <Message>
      <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
      <ReceiptHandle>+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qnn3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4PzHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0=</ReceiptHandle>
      <MD5OfBody>8b25734299a8efa7eeb74bf261bdc72d</MD5OfBody>
      <Body>Services_Amazon_SQS Unit Test</Body>
    </Message>
  </ReceiveMessageResult>
  <ResponseMetadata>
    <RequestId>c890aaba-51fc-4ab4-85d2-a935ea181f60</RequestId>
  </ResponseMetadata>
</ReceiveMessageResponse>
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

        $messages = $this->queue->receive(1, 500);

        $this->assertEquals(1, count($messages));

        $this->assertArrayHasKey(
            0,
            $messages,
            'Messages are not numerically indexed from 0.'
        );

        $message = $messages[0];

        $this->assertTrue(is_array($message), 'Message is not an array.');

        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('body', $message);
        $this->assertArrayHasKey('handle', $message);
    }

    // }}}
    // {{{ testReceiveWithInvalidVisibilityTimeout()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_InvalidTimeoutException
     */
    public function testReceiveWithInvalidVisibilityTimeout()
    {
        $this->queue->receive(1, 10000);
    }

    // }}}
    // {{{ testDeleteMessage()

    /**
     * @group message
     */
    public function testDeleteMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<DeleteMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>7eb71adb-362b-4ce4-a626-4dcd7ca0dfc2</RequestId>
  </ResponseMetadata>
</DeleteMessageResponse>
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

        $this->queue->delete(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0'
        );
    }

    // }}}
    // {{{ testDeleteInvalidMessage()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testDeleteInvalidMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>ReceiptHandleIsInvalid</Code>
    <Message>ReceiptHandleIsInvalid</Message>
    <Detail/>
  </Error>
  <RequestId>01ec860e-694c-4857-b1c2-61691129c09d</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 404 Not Found');

        $this->queue->delete('invalid-receipt-handle');
    }

    // }}}
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
<DeleteMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <ResponseMetadata>
    <RequestId>7eb71adb-362b-4ce4-a626-4dcd7ca0dfc2</RequestId>
  </ResponseMetadata>
</DeleteMessageResponse>
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

        $this->queue->delete(
            '+eXJYhj5rDqRunVNVvjOQKJ0obJP08UNsXdn2v3Lwq+' .
            'TDtD3hk3aBKbSH1mGc4hzO/VZOIC0RFzLWMLhfKh4qn' .
            'n3x35CTz9dLTiBp6rMQSSsfakSe+GcTkPfqzNJdCM4P' .
            'zHuhDaS9mXjcAcCzIRrOX9Mp5AiZxsfiLGqOsqhtH0'
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
}

?>
