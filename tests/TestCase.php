<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Tests for the Services_Amazon_SQS package.
 *
 * These tests require the PHPUnit 3.2 package to be installed. PHPUnit is
 * installable using PEAR. See the
 * {@link http://www.phpunit.de/pocket_guide/3.2/en/installation.html manual}
 * for detailed installation instructions.
 *
 * This test suite follows the PEAR AllTests conventions as documented at
 * {@link http://cvs.php.net/viewvc.cgi/pear/AllTests.php?view=markup}.
 *
 * Note:
 *
 *   These tests require access keys from Amazon.com. Enter your access keys
 *   in config.php to run these tests. If config.php is missing, these
 *   tests will be skipped. A sample configuration is provided in the file
 *   config.php.dist.
 *
 * Note:
 *
 *   Deleting queues, which happens in a several places in these unit tests,
 *   must be followed by a timeout to make sure that successive list requests
 *   do not return the banished queues.  This might make some tests extremely
 *   slow.
 *
 * LICENSE:
 *
 * Copyright 2008 Mike Brittain, silverorange
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
 * @copyright 2008 Mike Brittain, 2008 silverorange
 * @copyright 2008 silverorange
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
 * Tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_TestCase extends PHPUnit_Framework_TestCase
{
    // {{{ private properties

    /**
     * @var integer
     */
    private $_oldErrorLevel = 0;

    // }}}
    // {{{ class constants

    /**
     * Deletes can take up to 60 seconds, use this timeout after deletes during
     * test.
     */
    const TIMEOUT = 65;

    /**
     * Prefix used on queues created by these tests.
     */
    const PREFIX = 'Services_Amazon_SQS-test-';

    /**
     * String used for test messages.
     */
    const TEST_MESSAGE = 'Sample message right here, folks!';

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
        $configFilename = dirname(__FILE__).'/config.php';

        if (!file_exists($configFilename)) {
            $this->markTestSkipped('Unit test configuration is missing. ' .
                'Please read the documentation in TestCase.php and create a ' .
                'configuration file. See the configuration in ' .
                '\'config.php.dist\' for an example.');
        }

        include $configFilename;

        $failed = false;

        // make sure config array exists
        if (   !isset($GLOBALS['Services_Amazon_SQS_Unittest_Config'])
            || !is_array($GLOBALS['Services_Amazon_SQS_Unittest_Config'])
        ) {
            $failed = true;
        } else {
            $config = $GLOBALS['Services_Amazon_SQS_Unittest_Config'];
        }

        // make sure config array has required values
        if (   $failed
            || !isset($config['accessKey'])
            || !isset($config['secretAccessKey'])
        ) {
            $this->markTestSkipped('Unit test configuration is incorrect. ' .
                'Please read the documentation in TestCase.php and fix the ' .
                'configuration file. See the configuration in ' .
                '\'config.php.dist\' for an example.');
        }

        $this->_oldErrorLevel = error_reporting(E_ALL | E_STRICT);

        $this->accessKey       = $config['accessKey'];
        $this->secretAccessKey = $config['secretAccessKey'];
    }

    // }}}
    // {{{ tearDown()

    public function tearDown()
    {
        // delete all test queues
        $manager = $this->getQueueManager();
        $queues = $manager->listQueues(self::PREFIX);
        foreach ($queues as $queue) {
            $manager->deleteQueue($queue);
        }
        if (count($queues > 0)) {
            $this->sleep();
        }

        // restore error handling
        error_reporting($this->_oldErrorLevel);
    }

    // }}}

    // queue tests
    // {{{ testDeleteQueues()

    /**
     * @group queue
     */
    public function testDeleteQueues()
    {
        $manager = $this->getQueueManager();

        // create some queues
        foreach (range(1, 10) as $i) {
            $this->createTestQueue();
        }

        // maks sure they were created
        $queues = $manager->listQueues(self::PREFIX);
        $this->assertTrue(is_array($queues));
        $this->assertGreaterThanOrEqual(10, count($queues));

        // delete them
        foreach ($queues as $queue) {
            $manager->deleteQueue($queue);
        }

        $this->sleep();

        // make sure they were deleted
        $queues = $manager->listQueues(self::PREFIX);
        $this->assertTrue(is_array($queues));
        $this->assertEquals(0, count($queues));
    }

    // }}}
    // {{{ testListQueues()

    /**
     * @group queue
     */
    public function testListQueues()
    {
        $manager = $this->getQueueManager();

        // test listing without a prefix
        $queues = $manager->listQueues();
        $this->assertTrue(is_array($queues));

        // there should be no test queues
        $list = $manager->listQueues(self::PREFIX);
        $this->assertTrue(is_array($list));
        $this->assertEquals(0, count($list));
    }

    // }}}
    // {{{ testCreateQueue()

    /**
     * @group queue
     */
    public function testCreateQueue()
    {
        $manager = $this->getQueueManager();

        $queue = $this->createTestQueue();
        $this->assertType('Services_Amazon_SQS_Queue', $queue);

        $queue = $this->createTestQueue();
        $this->assertType('Services_Amazon_SQS_Queue', $queue);

        $this->sleep();

        $queues = $manager->listQueues(self::PREFIX);
        $this->assertTrue(is_array($queues));
        $this->assertGreaterThanOrEqual(2, count($queues));
    }

    // }}}

    // attribute tests
    // {{{ testGetAttributes_all()

    /**
     * @group attributes
     */
    public function testGetAttributes_all()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();

        // test getting all attributes
        $attributes = $queue->getAttributes();
        $this->assertTrue(is_array($attributes));
        $this->assertEquals(2, count($attributes));

        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertRegExp('/^\d+$/', $attributes['VisibilityTimeout'],
            'VisibilityTimeout is not an integer');

        $this->assertEquals(30, $attributes['VisibilityTimeout']);

        $this->assertArrayHasKey('ApproximateNumberOfMessages', $attributes);
        $this->assertRegExp('/^\d+$/',
            $attributes['ApproximateNumberOfMessages'],
            'ApproximateNumberOfMessages is not an integer');

        $this->assertEquals(0, $attributes['ApproximateNumberOfMessages']);
    }

    // }}}
    // {{{ testGetAttributes_VisibilityTimeout()

    /**
     * @group attributes
     */
    public function testGetAttributes_VisibilityTimeout()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();

        // test getting the 'VisibilityTimeout' attribute
        $attributes = $queue->getAttributes('VisibilityTimeout');
        $this->assertTrue(is_array($attributes));
        $this->assertEquals(1, count($attributes));

        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertRegExp('/^\d+$/',
            $attributes['VisibilityTimeout'],
            'VisibilityTimeout is not an integer');

        $this->assertEquals(30, $attributes['VisibilityTimeout']);
    }

    // }}}
    // {{{ testSetAttribute_invalid()

    /**
     * @group attributes
     */
    public function testSetAttribute_invalid()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();

        // test setting an invalid attribute
        $response = $queue->setAttribute('InvalidAttributeName', 1);
        $this->assertFalse($response);
    }

    // }}}
    // {{{ testSetAttribute_invalid_value()

    /**
     * @group attributes
     */
    public function testSetAttribute_invalid_value()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();

        // test setting an invalid attribute value
        $response = $queue->setAttribute('VisibilityTimeout', 10000);
        $this->assertFalse($response);
    }

    // }}}
    // {{{ testSetAttribute()

    /**
     * @group attributes
     */
    public function testSetAttribute()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();
        $value   = 200;

        // test setting 'VisibilityTimeout' attribute value
        $response = $queue->setAttribute('VisibilityTimeout', $value);
        $this->assertTrue($response);

        // check the attribute value
        $attributes = $queue->getAttributes('VisibilityTimeout');
        $this->assertArrayHasKey('VisibilityTimeout', $attributes);
        $this->assertRegExp('/^\d+$/',
            $attributes['VisibilityTimeout'],
            'VisibilityTimeout is not an integer');

        $this->assertEquals($value, $attributes['VisibilityTimeout']);
    }

    // }}}

    // message tests
    // {{{ testSendAndReceiveMessages()

    /**
     * @group message
     */
    public function testSendAndReceiveMessages()
    {
        $manager     = $this->getQueueManager();
        $queue       = $this->createTestQueue();
        $numMessages = 200;
        $chunkSize   = 20;
        $timeout     = 60;

        // add ten messages to the queue
        $timestamp = gmdate('c');
        for ($i = 0; $i < $numMessages; $i++) {
            $message = sprintf('Message %s: %s [%s]',
                $i, $timestamp, uniqid());

            $result = $queue->send($message);
            $this->assertTrue($result);
        }

        // retrieve the messages from the queue with a 60 second timeout
        $received = array();
        while (count($received) < $numMessages) {
            $messages = $queue->receive($chunkSize, $timeout);
            foreach ($messages as $message) {
                $this->assertTrue(is_array($message));
                $this->assertArrayHasKey('id', $message);
                $this->assertArrayHasKey('body', $message);
                $this->assertArrayHasKey('handle', $message);
            }
            $received = array_merge($received, $messages);
        }

        $this->assertEquals($numMessages, count($received));
    }

    // }}}
    // {{{ testVisibilityTimeout()

    /**
     * @group message
     */
    public function testVisibilityTimeout()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();
        $timeout = 5;

        // add test message to the queue
        $result = $queue->send(self::TEST_MESSAGE);
        $this->assertTrue($result);

        // retrieve test message with a five second visibility timeout
        $messages = array();
        while (count($messages) === 0) {
            $messages = $queue->receive(1, $timeout);
            $this->assertTrue(is_array($messages));
        }

        $message = $messages[0];
        $this->assertTrue(is_array($message));
        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('body', $message);
        $this->assertArrayHasKey('handle', $message);

        // make sure test message has the right body
        $this->assertEquals(self::TEST_MESSAGE, $message['body']);

        // try to retrieve again before five seconds are up, no messages should
        // be returned
        foreach (range(1, 10) as $i) {
            $messages = $queue->receive(1, $timeout);
            $this->assertTrue(is_array($messages));
            $this->assertEquals(0, count($messages));
        }

        // wait for timeout to expire
        sleep($timeout);

        // try to retrieve the message again, it should be successfully returned
        $messages = array();
        while (count($messages) === 0) {
            $messages = $queue->receive(1, $timeout);
            $this->assertTrue(is_array($messages));
        }
    }

    // }}}
    // {{{ testDeleteMessage()

    /**
     * @group message
     */
    public function testDeleteMessage()
    {
        $manager = $this->getQueueManager();
        $queue   = $this->createTestQueue();

        // add test message to the queue
        $result = $queue->send(self::TEST_MESSAGE);
        $this->assertTrue($result);

        // retrieve test message
        $messages = array();
        while (count($messages) === 0) {
            $messages = $queue->receive();
            $this->assertTrue(is_array($messages));
        }

        $message = $messages[0];
        $this->assertTrue(is_array($message));
        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('body', $message);
        $this->assertArrayHasKey('handle', $message);

        // make sure test message has the right body
        $this->assertEquals(self::TEST_MESSAGE, $message['body']);

        // delete the message
        $response = $queue->delete($message['handle']);
        $this->assertEquals(true, $response);

        // Note: We can't actually check if the message is still in the queue
        //       here. Amazon does not guarantee the message will actually be
        //       deleted.
    }

    // }}}

    // helper methods
    // {{{ getQueueManager()

    protected function getQueueManager()
    {
        return new Services_Amazon_SQS_QueueManager($this->accessKey,
            $this->secretAccessKey);
    }

    // }}}
    // {{{ createTestQueue()

    protected function createTestQueue()
    {
        $manager = $this->getQueueManager();
        $name = uniqid(self::PREFIX);
        $queue = $manager->createQueue($name);
        return $queue;
    }

    // }}}
    // {{{ sleep()

    protected function sleep()
    {
        sleep(self::TIMEOUT);
    }

    // }}}
}

?>
