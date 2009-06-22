<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Receive message tests for the Services_Amazon_SQS package.
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
 * Receive message tests for Services_Amazon_SQS
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
class Services_Amazon_SQS_ReceiveMessageTestCase extends
	Services_Amazon_SQS_TestCase
{
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
}

?>
