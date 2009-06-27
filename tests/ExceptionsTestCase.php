<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Exception tests for the Services_Amazon_SQS package.
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
 * Copyright 2009 silverorange
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
 * @copyright 2009 silverorange
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
 * Exception classes
 */
require_once 'Services/Amazon/SQS/Exceptions.php';

/**
 * Exception tests for Services_Amazon_SQS
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2009 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_ExceptionsTestCase extends
    Services_Amazon_SQS_TestCase
{
    // http exception
    // {{{ testHttpException

    /**
     * @group http-exception
     * @expectedException Services_Amazon_SQS_HttpException test exception
     */
    public function testHttpException()
    {
        throw new Services_Amazon_SQS_HttpException('test exception');
    }

    // }}}

    // error exception
    // {{{ testErrorException

    /**
     * @group error-exception
     * @expectedException Services_Amazon_SQS_ErrorException test exception
     */
    public function testErrorException()
    {
        throw new Services_Amazon_SQS_ErrorException('test exception');
    }

    // }}}
    // {{{ testErrorException_getError()

    /**
     * @group error-exception
     */
    public function testErrorException_getError()
    {
        $e = new Services_Amazon_SQS_ErrorException(
            'test exception',
            0,
            'InvalidQueueName'
        );

        $this->assertEquals('InvalidQueueName', $e->getError());
    }

    // }}}

    // invalid queue exception
    // {{{ testInvalidQueueException

    /**
     * @group invalid-queue-exception
     * @expectedException Services_Amazon_SQS_InvalidQueueException test exception
     */
    public function testInvalidQueueException()
    {
        throw new Services_Amazon_SQS_InvalidQueueException('test exception');
    }

    // }}}
    // {{{ testInvalidQueueException_getName()

    /**
     * @group invalid-queue-exception
     */
    public function testInvalidQueueException_getName()
    {
        $e = new Services_Amazon_SQS_InvalidQueueException(
            'test exception',
            0,
            'foo-bar'
        );

        $this->assertEquals('foo-bar', $e->getName());
    }

    // }}}

    // invalid attribute exception
    // {{{ testInvalidAttributeException

    /**
     * @group invalid-attribute-exception
     * @expectedException Services_Amazon_SQS_InvalidAttributeException test exception
     */
    public function testInvalidAttributeException()
    {
        throw new Services_Amazon_SQS_InvalidAttributeException(
            'test exception'
        );
    }

    // }}}
    // {{{ testInvalidAttributeException_getName()

    /**
     * @group invalid-attribute-exception
     */
    public function testInvalidAttributeException_getName()
    {
        $e = new Services_Amazon_SQS_InvalidAttributeException(
            'test exception',
            0,
            'foo-bar'
        );

        $this->assertEquals('foo-bar', $e->getName());
    }

    // }}}

    // invalid timeout exception
    // {{{ testInvalidTimeoutException

    /**
     * @group invalid-timeout-exception
     * @expectedException Services_Amazon_SQS_InvalidTimeoutException test exception
     */
    public function testInvalidTimeoutException()
    {
        throw new Services_Amazon_SQS_InvalidTimeoutException(
            'test exception'
        );
    }

    // }}}
    // {{{ testInvalidTimeoutException_getTimeout()

    /**
     * @group invalid-timeout-exception
     */
    public function testInvalidTimeoutException_getTimeout()
    {
        $e = new Services_Amazon_SQS_InvalidTimeoutException(
            'test exception',
            0,
            86200
        );

        $this->assertEquals(86200, $e->getTimeout());
    }

    // }}}

    // invalid message exception
    // {{{ testInvalidMessageException

    /**
     * @group invalid-message-exception
     * @expectedException Services_Amazon_SQS_InvalidMessageException test exception
     */
    public function testInvalidMessageException()
    {
        throw new Services_Amazon_SQS_InvalidMessageException(
            'test exception'
        );
    }

    // }}}
    // {{{ testInvalidMessageException_getMessageBody()

    /**
     * @group invalid-message-exception
     */
    public function testInvalidMessageException_getMessageBody()
    {
        $e = new Services_Amazon_SQS_InvalidMessageException(
            'test exception',
            0,
            "\x0e"
        );

        $this->assertEquals("\x0e", $e->getMessageBody());
    }

    // }}}

    // checksum exception
    // {{{ testChecksumException

    /**
     * @group checksum-exception
     * @expectedException Services_Amazon_SQS_ChecksumException test exception
     */
    public function testChecksumException()
    {
        throw new Services_Amazon_SQS_ChecksumException(
            'test exception'
        );
    }

    // }}}
    // {{{ testChecksumException_getMessageId()

    /**
     * @group checksum-exception
     */
    public function testChecksumException_getMessageId()
    {
        $e = new Services_Amazon_SQS_ChecksumException(
            'test exception',
            0,
            '012345678abcdef'
        );

        $this->assertEquals('012345678abcdef', $e->getMessageId());
    }

    // }}}
}

?>
