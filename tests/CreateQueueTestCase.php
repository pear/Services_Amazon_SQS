<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Create queue tests for the Services_Amazon_SQS package.
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
 * Create queue tests for Services_Amazon_SQS
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
class Services_Amazon_SQS_CreateQueueTestCase extends
    Services_Amazon_SQS_TestCase
{
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
    // {{{ testCreateQueueWithInvalidName()

    /**
     * @group queue
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testCreateQueueWithInvalidName()
    {
        $queue = $this->manager->createQueue('$queue');
    }

    // }}}
    // {{{ testCreateQueueWithMoribundName()

    /**
     * @group queue
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testCreateQueueWithMoribundName()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>AWS.SimpleQueueService.QueueDeletedRecently</Code>
    <Message>You must wait 60 seconds after deleting a queue before you can create another with the same name.</Message>
    <Detail/>
  </Error>
  <RequestId>1a09b0ef-e1ce-4d2a-bf23-bb20624f7e31</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');
        $queue = $this->manager->createQueue('foo');
    }

    // }}}
    // {{{ testCreateQueueWithDuplicateName()

    /**
     * @group queue
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testCreateQueueWithDuplicateName()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>QueueAlreadyExists</Code>
    <Message>A queue already exists with the same name and a different visibility timeout</Message>
    <Detail/>
  </Error>
  <RequestId>1a09b0ef-e1ce-4d2a-bf23-bb20624f7e31</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');
        $queue = $this->manager->createQueue('foo');
    }

    // }}}
    // {{{ testCreateQueueWithError()

    /**
     * @group queue
     * @expectedException Services_Amazon_SQS_ErrorException
     */
    public function testCreateQueueWithError()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>SignatureDoesNotMatch</Code>
    <Message>The request signature we calculated does not match the signature you provided. Check your AWS Secret Access Key and signing method. Consult the service documentation for details.</Message>
    <Detail/>
  </Error>
  <RequestId>1a09b0ef-e1ce-4d2a-bf23-bb20624f7e31</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 403 Forbidden');

        $manager = new Services_Amazon_SQS_QueueManager(
            '123456789ABCDEFGHIJK',
            'abcdefghijklmnopqrstuzwxyz/ABCDEFGHIJKLM',
            $this->request
        );

        $queue = $manager->createQueue('foo');
    }

    // }}}
}

?>
