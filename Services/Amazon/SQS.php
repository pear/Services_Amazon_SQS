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
    const SQS_API_VERSION = '2008-01-01';

    /**
     * Legacy parameter required by SQS.
     */
    const SQS_SIGNATURE_VERSION = '1';

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
     */
    protected $request = null;

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
        HTTP_Request2 $request = null)
    {
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
    // {{{ addRequiredParameters()

    /**
     * Adds required authentication and version parameters to an array of
     * parameters
     *
     * The required parameters are:
     * - AWSAccessKey
     * - SignatureVersion
     * - Timestamp
     * - Version and
     * - Signature
     *
     * If a required parameter is already set in the <tt>$parameters</tt> array,
     * it is not overwritten.
     *
     * @param array $parameters the array to which to add the required
     *                          parameters.
     *
     * @return void
     */
    protected function addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->account->getAccessKey();
        $parameters['SignatureVersion'] = self::SQS_SIGNATURE_VERSION;
        $parameters['Timestamp']        = $this->_getFormattedTimestamp();
        $parameters['Version']          = self::SQS_API_VERSION;
        $parameters['Signature']        = $this->signParameters($parameters,
                                          $this->account->getSecretAccessKey());

        return $parameters;
    }

    // }}}
    // {{{ signParameters()

    /**
     * Computes the RFC 2104-compliant HMAC signature for request parameters
     *
     * This implements the Amazon Web Services signature, as per the following
     * specification:
     *
     * 1. Sort all request parameters (including <tt>SignatureVersion</tt> and
     *    excluding <tt>Signature</tt>, the value of which is being created),
     *    ignoring case.
     *
     * 2. Iterate over the sorted list and append the parameter name (in its
     *    original case) and then its value. Do not URL-encode the parameter
     *    values before constructing this string. Do not use any separator
     *    characters when appending strings.
     *
     * @param array  $parameters the parameters for which to get the signature.
     * @param string $secretKey  the secret key to use to sign the parameters.
     *
     * @return string the signature to the specified parameters. Use this value
     *                for the <tt>Signature</tt> parameter.
     */
    protected function signParameters(array $parameters, $secretKey)
    {
        $data = '';

        uksort($parameters, 'strcasecmp');
        unset($parameters['Signature']);

        foreach ($parameters as $key => $value) {
            $data .= $key . $value;
        }

        return $this->_sign($data, $secretKey);
    }

    // }}}
    // {{{ sendRequest()

    /**
     * Sends a HTTP request to the queue service using cURL
     *
     * The supplied <tt>$params</tt> array should contain only the specific
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

        try {
            /*
             * Note: The request object is only used as a template to create
             * other request objects. This prevents one API call from affecting
             * the state of the HTTP request object for subsequent API calls.
             */
            $request = clone $this->request;

            $request->setConfig(array(
                'connect_timeout' => self::HTTP_TIMEOUT
            ));

            $request->setUrl($url);
            $request->setMethod(HTTP_Request2::METHOD_POST);
            $request->setHeader('User-Agent', $this->_getUserAgent());
            $request->addPostParameter($params);

            $httpResponse = $request->send();

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
    // {{{ _sign()

    /**
     * Computes the RFC 2104-compliant HMAC signature for a string
     *
     * @param string $data      the data for which to compute the hash.
     * @param string $secretKey the secret key used to sign the data.
     *
     * @return string the signed data.
     */
    private function _sign($data, $secretKey)
    {
        $hmac = new Crypt_HMAC2($secretKey, 'SHA1');

        // get raw hash
        $hash = $hmac->hash($data, Crypt_HMAC2::BINARY);

        // Amazon wants the value base64-encoded
        return base64_encode($hash);
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
    // {{{ _checkForErrors()

    /**
     * Checks for errors responses from Amazon
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
