<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Abstract base class for tests for the Services_Amazon_SQS package.
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
 * Abstract base class for tests for Services_Amazon_SQS
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
abstract class Services_Amazon_SQS_TestCase extends PHPUnit_Framework_TestCase
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
    // {{{ private properties

    /**
     * @var integer
     */
    private $_oldErrorLevel = 0;

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
}

?>
