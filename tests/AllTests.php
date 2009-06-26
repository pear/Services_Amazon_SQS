<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit 3.2 AllTests suite for the Services_Amazon_SQS package.
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
 * LICENSE:
 *
 * Copyright 2008 silverorange
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
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Services_Amazon_SQS_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once dirname(__FILE__) . '/ChangeMessageVisibilityTestCase.php';
require_once dirname(__FILE__) . '/ConstructTestCase.php';
require_once dirname(__FILE__) . '/CreateQueueTestCase.php';
require_once dirname(__FILE__) . '/DeleteMessageTestCase.php';
require_once dirname(__FILE__) . '/DeleteQueueTestCase.php';
require_once dirname(__FILE__) . '/GetAttributesTestCase.php';
require_once dirname(__FILE__) . '/ListQueuesTestCase.php';
require_once dirname(__FILE__) . '/ReceiveMessageTestCase.php';
require_once dirname(__FILE__) . '/ResponseTestCase.php';
require_once dirname(__FILE__) . '/SendMessageTestCase.php';
require_once dirname(__FILE__) . '/SetAttributesTestCase.php';

/**
 * AllTests suite testing Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_AllTests
{
    // {{{ main()

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    // }}}
    // {{{ suite()

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Services_Amazon_SQS Tests');

        $suite->addTestSuite('Services_Amazon_SQS_ChangeMessageVisibilityTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_ConstructTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_CreateQueueTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_DeleteMessageTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_DeleteQueueTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_GetAttributesTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_ListQueuesTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_ResponseTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_ReceiveMessageTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_SendMessageTestCase');
        $suite->addTestSuite('Services_Amazon_SQS_SetAttributesTestCase');

        return $suite;
    }

    // }}}
}

if (PHPUnit_MAIN_METHOD == 'Services_Amazon_SQS_AllTests::main') {
    Services_Amazon_SQS_AllTests::main();
}

?>
