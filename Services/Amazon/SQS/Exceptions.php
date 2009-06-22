<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file contains various exception classes used by the Services_Amazon_SQS
 * package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright silverorange
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
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * PEAR exception base class
 */
require_once 'PEAR/Exception.php';

// {{{ class Services_Amazon_SQS_Exception

/**
 * Abstract base class for exceptions thrown by the Services_Amazon_SQS package
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
abstract class Services_Amazon_SQS_Exception extends PEAR_Exception
{
}

// }}}
// {{{ class Services_Amazon_SQS_HttpException

/**
 * Exception thrown when there is a HTTP communication error in the
 * Services_Amazon_SQS package
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_HttpException extends Services_Amazon_SQS_Exception
{
}

// }}}
// {{{ class Services_Amazon_SQS_ErrorException

/**
 * Exception thrown when one or more errors are returned by Amazon
 *
 * The Amazon error code may be retrived using
 * {@link Services_Amazon_SQS_ErrorException::getCode()} and the error message
 * may bre retrieved using
 * {@link Services_Amazon_SQS_ErrorException::getMessage()}.
 *
 * See the Amazon SQS Developer's Guide for a full list of error codes.
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_ErrorException extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The Amazon SQS error code
     *
     * @var string
     */
    private $_error = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new error exception
     *
     * @param string  $message an error message.
     * @param integer $code    a user-defined error code.
     * @param string  $error   the Amazon SQS error code.
     */
    public function __construct($message, $code, $error = '')
    {
        parent::__construct($message, $code);
        $this->_error = $error;
    }

    // }}}
    // {{{ getError()

    /**
     * Gets the Amazon SQS error code
     *
     * @return string the Amazon SQS error code.
     */
    public function getError()
    {
        return $this->_error;
    }

    // }}}
}

// }}}
// {{{ class Services_Amazon_SQS_InvalidQueueException

/**
 * Exception thrown when an invalid queue is used in the Services_Amazon_SQS
 * package
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_InvalidQueueException
    extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The name of the queue that is invalid
     *
     * @var string
     */
    private $_name = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid queue exception
     *
     * @param string  $message an error message.
     * @param integer $code    a user-defined error code.
     * @param string  $name    the name of the queue that is invalid.
     */
    public function __construct($message, $code, $name = '')
    {
        parent::__construct($message, $code);
        $this->_name = $name;
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the queue that is invalid
     *
     * @return string the name of the queue that is invalid.
     */
    public function getName()
    {
        return $this->_name;
    }

    // }}}
}

// }}}
// {{{ class Services_Amazon_SQS_InvalidAttributeException

/**
 * Exception thrown when an invalid attribute is used in the
 * Services_Amazon_SQS package
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_InvalidAttributeException
    extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The name of the attribute that is invalid
     *
     * @var string
     */
    private $_name = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid attribute exception
     *
     * @param string  $message an error message.
     * @param integer $code    a user-defined error code.
     * @param string  $name    the name of the attribute that is invalid.
     */
    public function __construct($message, $code, $name = '')
    {
        parent::__construct($message, $code);
        $this->_name = $name;
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the attribute that is invalid
     *
     * @return string the name of the attribute that is invalid.
     */
    public function getName()
    {
        return $this->_name;
    }

    // }}}
}

// }}}
// {{{ class Services_Amazon_SQS_InvalidTimeoutException

/**
 * Exception thrown when an invalid timeout is used in the Services_Amazon_SQS
 * package
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_InvalidTimeoutException
    extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The invalid timeout value
     *
     * @var integer
     */
    private $_timeout = 0;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid timeout exception
     *
     * @param string  $message an error message.
     * @param integer $code    a user-defined error code.
     * @param integer $timeout the invalid timeout value.
     */
    public function __construct($message, $code, $timeout = 0)
    {
        parent::__construct($message, $code);
        $this->_timeout = $timeout;
    }

    // }}}
    // {{{ getTimeout()

    /**
     * Gets the invalid timeout value
     *
     * @return integer the invalid timeout value.
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    // }}}
}

// }}}
// {{{ class Services_Amazon_SQS_InvalidMessageException

/**
 * Exception thrown when a message body is invalid
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_InvalidMessageException
    extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The invalid message body
     *
     * @var string
     */
    private $_messageBody = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid message exception
     *
     * @param string  $message     an error message.
     * @param integer $code        a user-defined error code.
     * @param integer $messageBody the invalid message body.
     */
    public function __construct($message, $code, $messageBody = '')
    {
        parent::__construct($message, $code);
        $this->_messageBody = $messageBody;
    }

    // }}}
    // {{{ getMessageBody()

    /**
     * Gets the invalid message body
     *
     * @return string the invalid message body.
     */
    public function getMessageBody()
    {
        return $this->_messageBody;
    }

    // }}}
}

// }}}
// {{{ class Services_Amazon_SQS_ChecksumException

/**
 * Exception thrown when there is a message body checksum mismatch
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */
class Services_Amazon_SQS_ChecksumException
    extends Services_Amazon_SQS_Exception
{
    // {{{ private properties

    /**
     * The id of the message that had the checksum mismatch
     *
     * @var string
     */
    private $_messageId = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new checksum mismatch exception
     *
     * @param string  $message   an error message.
     * @param integer $code      a user-defined error code.
     * @param integer $messageId the id of the message with the checksum
     *                           mismatch.
     */
    public function __construct($message, $code, $messageId = '')
    {
        parent::__construct($message, $code);
        $this->_messageId = $messageId;
    }

    // }}}
    // {{{ getMessageId()

    /**
     * Gets the id of the message that has the checksum mismatch
     *
     * @return string the id of the message that has the checksum mismatch.
     */
    public function getMessageId()
    {
        return $this->_messageId;
    }

    // }}}
}

// }}}

?>
