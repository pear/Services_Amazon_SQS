<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the class definition for the abstract base class for interfacing
 * with Amazon Simple Queue Service (SQS) queues
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright 2008 Mike Brittain, Amazon.com, silverorange
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
 *   Portions of this code were taken from the Amazon SQS PHP5 Library which
 *   is distributed under the Apache 2.0 license
 *   (http://aws.amazon.com/apache2.0).
 *
 *   See {@link http://developer.amazonwebservices.com/connect/entry.jspa?externalID=1287&categoryID=190}
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 Amazon.com, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * Exception classes.
 */
require_once 'Services/Amazon/SQS/Exceptions.php';

/**
 * SQS account
 */
require_once 'Services/Amazon/SQS/Account.php';

/**
 * SQS response object
 */
require_once 'Services/Amazon/SQS/Response.php';

/**
 * For HMAC hashing
 */
require_once 'Crypt/HMAC2.php';

/**
 * For making HTTP requests.
 */
require_once 'HTTP/Request2.php';

/**
 * Used for building the request signature.
 */
require_once 'Net/URL2.php';

/**
 * Abstract base class for interfacing with Amazon Simple Queue Service (SQS)
 * queues
 *
 * This class uses the HTTP query mechanism for accessing the Amazon SQS. See
 * page 20 of the Amazon SQS Developer's Guide PDF for details about the HTTP
 * query mechanism.
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008 Amazon.com, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
abstract class Services_Amazon_SQS
{
    // {{{ class constants

    /**
     * The HTTP query server.
     */
    const SQS_SERVER = 'queue.amazonaws.com';

    /**
     * The API version to use.
     */
    const SQS_API_VERSION = '2009-02-01';

    /**
     * The signature version used by Services_Amazon_SQS.
     *
     * @see http://developer.amazonwebservices.com/connect/entry.jspa?externalID=1928
     */
    const SQS_SIGNATURE_VERSION = '2';

    /**
     * Period after which HTTP requests will timeout in seconds.
     */
    const HTTP_TIMEOUT = 10;

    // }}}
    // {{{ protected properties

    /**
     * The account to use
     *
     * @var Services_Amazon_SQS_Account
     */
    protected $account = null;

    /**
     * The HTTP request object to use
     *
     * This can be specified in the constructor. Note: The request object is
     * only used as a template to create other request objects. This prevents
     * one API call from affecting the state of the HTTP request object for
     * subsequent API calls.
     *
     * @var HTTP_Request2
     *
     * @see Services_Amazon_SQS::setRequest()
     */
    protected $request = null;

    /**
     * The maximum number of retries to make when encountering internal errors
     * on SQS
     *
     * @var integer
     *
     * @see Services_Amazon_SQS::setMaximumRetries()
     */
    protected $maximumRetries = 10;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new SQS client
     *
     * @param Services_Amazon_SQS_Account|string $accessKey       either a
     *        {@link Services_Amazon_SQS_Account} object or a string containing
     *        the SQS access key for an account.
     *
     * @param string                             $secretAccessKey if the first
     *        parameter is an account object, this parameter is ignored.
     *        Otherwise, this parameter is required and is the secret access
     *        key for the SQS account.
     *
     * @param HTTP_Request2                      $request         optional. The
     *        HTTP request object to use. If not specified, a HTTP request
     *        object is created automatically.
     */
    public function __construct($accessKey, $secretAccessKey = '',
        HTTP_Request2 $request = null
    ) {
        // set account object
        if ($accessKey instanceof Services_Amazon_SQS_Account) {
            $this->account = $accessKey;
        } else {
            if ($secretAccessKey == '') {
                throw new InvalidArgumentException(
                    'If accesKey is specified, secretAccessKey must be ' .
                    'specified as well.');
            }

            $this->account = new Services_Amazon_SQS_Account($accessKey,
                $secretAccessKey);
        }

        // set http request object
        if ($request === null) {
            $request = new HTTP_Request2();
        }

        $this->setRequest($request);
    }

    // }}}
    // {{{ setRequest()

    /**
     * Sets the HTTP request object to use
     *
     * @param HTTP_Request2 $request the HTTP request object to use.
     *
     * @return void
     */
    public function setRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
    }

    // }}}
    // {{{ setMaximumRetries()

    /**
     * Sets the maximum number of retries to make when encountering internal
     * errors on SQS
     *
     * Internal errors are handled using an exponential backoff algorithm so
     * take care not to set this number too high. For example, a request
     * requiring 20 retries will take approximately 17 minutes and 30 seconds
     * to complete. Any number less than 10 is generally acceptable, incurring
     * a maximum delay of just over 1 second.
     *
     * The default value for Services_Amazon_SQS is 10 retries. Set to 0 to
     * turn off exponential backoff altogether.
     *
     * @param integer $retries the maximum number of retries to make when
     *                         encountering internal errors on SQS.
     *
     * @return void
     */
    public function setMaximumRetries($retries)
    {
        $this->maximumRetries = intval($retries);
    }

    // }}}
    // {{{ sendRequest()

    /**
     * Sends a HTTP request to the queue service
     *
     * The supplied <kbd>$params</kbd> array should contain only the specific
     * parameters for the request type and should not include account,
     * signature, or timestamp related parameters. These parameters are added
     * automatically.
     *
     * @param array  $params   optional. Array of request parameters for the
     *                         API call.
     * @param string $queueUrl optional. The specific queue URL for which
     *                         the request is made. Does not need to be
     *                         specified for general actions like listing
     *                         queues.
     *
     * @return mixed Services_Amazon_SQS_Response object or false if the
     *               request failed.
     *
     * @throws Services_Amazon_SQS_HttpException if the HTTP request fails.
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned from Amazon.
     */
    protected function sendRequest(array $params = array(), $queueUrl = null)
    {
        $url = ($queueUrl) ? $queueUrl : 'http://' . self::SQS_SERVER . '/';

        $params = $this->addRequiredParameters($params);

        $secretKey = $this->account->getSecretAccessKey();
        $params    = $this->signParameters($params, $secretKey, $url);

        try {
            /*
             * Note: The request object is only used as a template to create
             * other request objects. This prevents one API call from affecting
             * the state of the HTTP request object for subsequent API calls.
             */
            $request = clone $this->request;

            $request->setConfig(
                array(
                    'connect_timeout' => self::HTTP_TIMEOUT
                )
            );

            $request->setUrl($url);
            $request->setMethod(HTTP_Request2::METHOD_POST);
            $request->setHeader('User-Agent', $this->_getUserAgent());
            $request->addPostParameter($params);

            $count = 0;
            while ($count < $this->maximumRetries) {
                // exponential backoff delay
                usleep($this->getRequestBackoffTime($count) * 1000);

                // send request
                $httpResponse = $request->send();

                // check for internal errors
                if (!$this->_hasInternalError($httpResponse)) {
                    // no errors, continue processing the response
                    break;
                }

                // increment count for next request retry
                $count++;
            }

            // make sure the request object gets dereferenced
            unset($request);

        } catch (HTTP_Request2_Exception $e) {
            // throw an exception if there was an HTTP error
            $message = 'Error in request to AWS service: ' . $e->getMessage();
            throw new Services_Amazon_SQS_HttpException($message,
                $e->getCode());
        }

        $response = new Services_Amazon_SQS_Response($httpResponse);

        $this->_checkForErrors($response);

        return $response;
    }

    // }}}
    // {{{ addRequiredParameters()

    /**
     * Adds required authentication and version parameters to an array of
     * parameters
     *
     * The required parameters are:
     *
     * - <kbd>AWSAccessKey</kbd>,
     * - <kbd>Timestamp</kbd>, and
     * - <kbd>Version</kbd>.
     *
     * If a required parameter is already set in the <kbd>$parameters</kbd>
     * array, it is not overwritten.
     *
     * @param array $parameters the array to which to add the required
     *                          parameters.
     *
     * @return array the parameters array, which includes the required
     *               parameters.
     */
    protected function addRequiredParameters(array $parameters)
    {
        if (!array_key_exists('AWSAccessKeyId', $parameters)) {
            $parameters['AWSAccessKeyId'] = $this->account->getAccessKey();
        }

        if (!array_key_exists('Timestamp', $parameters)) {
            $parameters['Timestamp'] = $this->_getFormattedTimestamp();
        }

        if (!array_key_exists('Version', $parameters)) {
            $parameters['Version'] = self::SQS_API_VERSION;
        }

        return $parameters;
    }

    // }}}
    // {{{ signParameters()

    /**
     * Signs an array of request parameters using the Amazon Web Services
     * Signature Version 2 signing method
     *
     * @param array  $parameters the parameters for which to get the signature.
     * @param string $secretKey  the secret key to use to sign the parameters.
     * @param string $url        the request URI.
     *
     * @return array the signed parameters array. This method will add or set
     *               the keys <kbd>SignatureVersion</kbd>,
     *               <kbd>SignatureMethod</kbd> and <kbd>Signature</kbd>.
     *
     * @see http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
     */
    protected function signParameters(array $parameters, $secretKey, $url)
    {
        unset($parameters['Signature']);
        unset($parameters['SignatureVersion']);
        unset($parameters['SignatureMethod']);

        // figure out what hmac algorithm to use
        try {
            // try first to use SHA-256
            $hmac   = new Crypt_HMAC2($secretKey, 'SHA256');
            $method = 'HmacSHA256';
        } catch (Crypt_HMAC2_Exception $e) {
            // if SHA-256 is not available, use SHA-1
            $hmac   = new Crypt_HMAC2($secretKey, 'SHA1');
            $method = 'HmacSHA1';
        }

        $parameters['SignatureVersion'] = self::SQS_SIGNATURE_VERSION;
        $parameters['SignatureMethod']  = $method;

        $data = $this->_getStringToSign($parameters, $url);

        $signature = $hmac->hash($data, Crypt_HMAC2::BINARY);

        // Amazon wants the signature value base64-encoded
        $signature = base64_encode($signature);

        $parameters['Signature'] = $signature;

        return $parameters;
    }

    // }}}
    // {{{ isValidVisibilityTimeout()

    /**
     * Gets whether or not a visibility timeout is valid
     *
     * Visibility timeouts must be between 0 and 7200 seconds.
     *
     * @param integer $timeout the timeout value to check (in seconds).
     *
     * @return boolean true if the timeout is valid, otherwise false.
     */
    protected function isValidVisibilityTimeout($timeout)
    {
        $valid = true;

        if ($timeout < 0 || $timeout > 7200) {
            $valid = false;
        }

        return $valid;
    }

    // }}}
    // {{{ isValidPermissionLabel()

    /**
     * Gets whether or not a permission label is valid
     *
     * Amazon SQS permission labels must conform to the following rules:
     * - must be 1 to 80 ASCII characters
     * - must contain only alphanumeric characters, dashes (-), and
     *   underscores (_).
     *
     * @param string $label the permission label to check.
     *
     * @return boolean true if the permission label is valid, otherwise false.
     */
    protected function isValidPermissionLabel($label)
    {
        $valid = true;

        if (preg_match('/^[A-Za-z0-9\-\_]{1,80}$/', $label) === 0) {
            $valid = false;
        }

        return $valid;
    }

    // }}}
    // {{{ getRequestBackoffTime()

    /**
     * Gets the number of milliseconds to wait before the next reqeust when
     * performing exponential backoff
     *
     * Exponential backoff is used when SQS encounters internal errors.
     *
     * @param integer $count the number of the current attempt. Zero based.
     *
     * @return integer the number of milliseconds to delay before the next
     *                 request is attempted.
     */
    protected function getRequestBackoffTime($count)
    {
        return intval(pow(2, $count) - 1);
    }

    // }}}
    // {{{ _getStringToSign()

    /**
     * Gets the string to sign for Amazon Signature Version 2
     *
     * @param array  $parameters the request parameters.
     * @param string $url        the request URI.
     *
     * @return string the string to sign.
     *
     * @see http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
     */
    private function _getStringToSign(array $parameters, $url)
    {
        // sort parameters by key using natural byte-ordering
        uksort($parameters, array(__CLASS__, '_byteCompare'));

        // encode parameters
        $encodedParameters = '';
        foreach ($parameters as $key => $value) {
            $encodedParameters .= sprintf(
                '&%s=%s',
                $this->_urlencode($key),
                $this->_urlencode($value)
            );
        }

        // remove leading ampersand
        $encodedParameters = substr($encodedParameters, 1);

        // get host and path
        $url = new Net_URL2($url);

        $method = 'POST';
        $host   = $url->getHost();
        $path   = $url->getPath();
        $path   = ($path === '') ? '/' : $path;

        // build the string to sign
        $data = sprintf(
            "%s\n%s\n%s\n%s",
            $method,
            strtolower($host),
            $path,
            $encodedParameters
        );

        return $data;
    }

    // }}}
    // {{{ _urlencode()

    /**
     * URL-encodes a string according to RFC 3986
     *
     * PHP's rawurlencode() uses RFC 1738. Amazon's signatures require the use
     * of the more recent RFC 3986. The main differece from the perspective of
     * URL encoding is that the tilde (<kbd>~</kbd>) is now unreserved. As of
     * PHP 5.3, the rawurlencode() function will use RFC 3986.
     *
     * @param string $string the string to encode.
     *
     * @return the string URL encoded according to RFC 3986.
     *
     * @see http://www.ietf.org/rfc/rfc3986.txt (Chapter 2)
     */
    private function _urlencode($string)
    {
        $encoded = rawurlencode($string);

        // This str_replace() shouldn't have any affect on PHP 5.3 and is
        // probably faster than the alternative version_compare() call.
        $encoded = str_replace('%7E', '~', $encoded);

        return $encoded;
    }

    // }}}
    // {{{ _byteCompare()

    /**
     * Compares two strings using natural byte-ordering
     *
     * This should be compatible with PHP6 as it uses a binary cast before
     * comparing.
     *
     * @param string $string1 the first string.
     * @param string $string2 the second string.
     *
     * @return integer -1 if the first string is less than the second string,
     *                 1 if the first string is greater than the second string
     *                 or 0 if the strings are equal.
     */
    private static function _byteCompare($string1, $string2)
    {
        return strcmp((binary)$string1, (binary)$string2);
    }

    // }}}
    // {{{ _getFormattedTimestamp()

    /**
     * Gets the current time in UTC formatted using ISO-8601
     *
     * @return string the current time in UTC formatted using ISO-8601.
     */
    private function _getFormattedTimestamp()
    {
        return gmdate('c');
    }

    // }}}
    // {{{ _getUserAgent()

    /**
     * Gets the HTTP user-agent used to make requests on the Amazon SQS
     *
     * @return string the HTTP user-agent used to make requests.
     */
    private function _getUserAgent()
    {
        return '@name@/@api-version@';
    }

    // }}}
    // {{{ _hasInternalError()

    /**
     * Checks for internal error responses from Amazon
     *
     * @param HTTP_Request2_Response $response the response object to check.
     *
     * @return boolean true if an internal error is detected, otherwise false.
     */
    private function _hasInternalError(HTTP_Request2_Response $response)
    {
        return ($response->getStatus() >= 500 && $response->getStatus() < 600);
    }

    // }}}
    // {{{ _checkForErrors()

    /**
     * Checks for error responses from Amazon
     *
     * @param Services_Amazon_SQS_Response $response the response object to
     *                                               check.
     *
     * @return void
     *
     * @throws Services_Amazon_SQS_ErrorException if one or more errors are
     *         returned from Amazon.
     */
    private function _checkForErrors(Services_Amazon_SQS_Response $response)
    {
        $xpath = $response->getXPath();
        $list  = $xpath->query('//sqs:Error');
        if ($list->length > 0) {
            $node    = $list->item(0);
            $code    = $xpath->evaluate('string(sqs:Code/text())', $node);
            $message = $xpath->evaluate('string(sqs:Message/text())', $node);
            throw new Services_Amazon_SQS_ErrorException($message, 0, $code);
        }
    }

    // }}}
}

?>
