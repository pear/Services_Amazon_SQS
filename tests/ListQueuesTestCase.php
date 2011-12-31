<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * List queues tests for the Services_Amazon_SQS package.
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
 * List queues tests for Services_Amazon_SQS
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
class Services_Amazon_SQS_ListQueuesTestCase extends
    Services_Amazon_SQS_TestCase
{
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
}

?>

