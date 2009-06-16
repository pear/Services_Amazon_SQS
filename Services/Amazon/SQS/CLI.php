<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Services_Amazon_SQS command-line interface for managing Amazon
 * Simple Queue Service (SQS) queues
 *
 * PHP version 5
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
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */

/**
 * Command line parsing.
 */
require_once 'Console/CommandLine.php';

/**
 * Exception classes.
 */
require_once 'Services/Amazon/SQS/Exceptions.php';

/**
 * Queue manager class.
 */
require_once 'Services/Amazon/SQS/QueueManager.php';

/**
 * For loading config file.
 */
require_once 'PEAR/Config.php';

/**
 * Amazon Simple Queue Service (SQS) command-line interface
 *
 * This application allows you to create, delete and list queues on the
 * Simple Queue Service. A completed configuration file must be installed for
 * this application to run properly.
 *
 * @category  Services
 * @package   Services_Amazon_SQS
 * @author    Mike Brittain <mike@mikebrittain.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 Mike Brittain, 2008-2009 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_CLI
{
    // {{{ private properties

    /**
     * The access key id
     *
     * @var string
     */
    private $_accessKey = '';

    /**
     * The secret access key
     *
     * @var string
     */
    private $_secretAccessKey = '';

    /**
     * Queue manager
     *
     * @var Services_Amazon_SQS_QueueManager
     */
    private $_manager = null;

    /**
     * Configuration filename
     *
     * @var string
     */
    private $_configFilename = '';

    // }}}
    // {{{ singleton()

    /**
     * Gets the singleton instance of the SQS command-line application
     *
     * @return Services_Amazon_SQS the singleton instance of the SQS
     *                             command-line application.
     */
    public static function singleton()
    {
        static $app = null;
        if ($app === null) {
            $app = new self();
        }
        return $app;
    }

    // }}}
    // {{{ run()

    /**
     * Runs this application
     *
     * @return void
     */
    public function run()
    {
        $parser = Console_CommandLine::fromXmlFile($this->_getUiXml());

        try {
            $result = $parser->parse();

            $this->_setOptions($result->options);
            $this->_loadConfig();
            $this->_runCommand($parser, $result);

        } catch (Console_CommandLine_Exception $e) {
            $parser->displayError($e->getMessage());
        }
    }

    // }}}
    // {{{ _setOptions()

    /**
     * Sets the options
     *
     * Options are set from an array of named values. Available option names
     * are:
     *
     * - <kbd>string config</kbd> - the path to the configuration file to use.
     *
     * @param array $options optional. An associative array of containing the
     *                       options to use.
     *
     * @return void
     */
    private function _setOptions(array $options)
    {
        if (   array_key_exists('config', $options)
            && $options['config'] !== null
        ) {
            $this->_configFilename = strval($options['config']);
        }
    }

    // }}}
    // {{{ _runCommand()

    /**
     * Runs the specified command for this application
     *
     * @param Console_CommandLine        $parser the command-line interface
     *                                           parser.
     * @param Console_CommandLine_Result $result the results of parsing the
     *                                           current command line.
     *
     * @return void
     */
    private function _runCommand(Console_CommandLine $parser,
        Console_CommandLine_Result $result
    ) {
        $command = $result->command;

        switch ($result->command_name) {
        case 'create':
            $this->_createQueue($command->args['queue_name']);
            break;

        case 'delete':
            $this->_deleteQueue($command->args['queue_uri']);
            break;

        case 'help':
            $this->_help($parser, $result);
            break;

        case 'list':
            $headers = ($command->options['no_headers']) ? false : true;
            $this->_listQueues(
                $command->args['prefix'],
                $headers
            );
            break;

        case 'send':
            $this->_send($command->args['queue_uri']);
            break;

        case 'receive':
            $delete  = ($command->options['delete']) ? true : false;
            if ($command->options['timeout'] === null) {
                $timeout = 30;
            } else {
                $timeout = intval($command->options['timeout']);
            }
            $this->_receive(
                $command->args['queue_uri'],
                $delete,
                $timeout
            );
            break;

        case 'version':
            $parser->displayVersion();
            break;

        default:
            break;
        }
    }

    // }}}
    // {{{ _help()

    /**
     * Displays general command-line usage help
     *
     * @param Console_CommandLine        $parser the command-line interface
     *                                           parser.
     * @param Console_CommandLine_Result $result the results of parsing the
     *                                           current command line.
     *
     * @return void
     */
    private function _help(Console_CommandLine $parser,
        Console_CommandLine_Result $result
    ) {
        $subCommand = $result->command->args['command'];
        if ($subCommand) {
            if (array_key_exists($subCommand, $parser->commands)) {
                $command = $parser->commands[$subCommand];
                $command->displayUsage();
            } else {
                echo "Command \"" . $subCommand . "\" is not valid. " .
                    "Try \"sqs help\n";

                exit(1);
            }
        } else {
            $parser->displayUsage();
        }
    }

    // }}}
    // {{{ _listQueues()

    /**
     * Lists SQS queues
     *
     * @param string  $prefix      optional. Only list queues whose name begins
     *                             with the given prefix. If not specified, all
     *                             queues are returned.
     * @param boolean $showHeaders optional. Whether or not to show list headers
     *                             in output. If not specified, list headers are
     *                             shown.
     *
     * @return void
     */
    private function _listQueues($prefix = '', $showHeaders = true)
    {
        $manager = $this->_getQueueManager();

        try {
            $queues = $manager->listQueues($prefix);
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }

        if (count($queues) === 0) {
            $this->_output("No queues available.\n", STDERR);
        } else {

            // getting queue attributes can take some time so collect all
            // the values here before displaying the output
            $rows = array();
            foreach ($queues as $queue) {
                try {
                    $attributes = $queue->getAttributes();
                } catch (Services_Amazon_SQS_Exception $e) {
                    $this->_handleException($e);
                }

                $row            = array();
                $row['name']    = strval($queue);
                $row['number']  = $attributes['ApproximateNumberOfMessages'];
                $row['timeout'] = $attributes['VisibilityTimeout'];

                $rows[] = $row;
            }

            $format = "%-55s  %-10s  %-10s\n";

            // display headers
            if ($showHeaders) {
                $this->_output(sprintf($format, '', 'ITEMS', 'VIS.'));
                $this->_output(
                    sprintf($format, 'QUEUE NAME', '(APPROX.)', 'TIMEOUT')
                );
            }

            // display rows
            foreach ($rows as $row) {
                $this->_output(
                    sprintf(
                        $format,
                        $row['name'],
                        $row['number'],
                        $row['timeout']
                    )
                );
            }
        }
    }

    // }}}
    // {{{ _createQueue()

    /**
     * Creates a new queue on the SQS
     *
     * @param string $name the name of the queue to create
     *
     * @return void
     */
    private function _createQueue($name)
    {
        $manager = $this->_getQueueManager();

        try {
            $result = $manager->createQueue($name);
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }

        $this->_output(
            "New queue has been added. It may take up to 60 seconds for the " .
            "new queue to appear in the list of queues.\n"
        );
    }

    // }}}
    // {{{ _deleteQueue()

    /**
     * Deletes a queue from the SQS
     *
     * @param string $url the Queue URL of the queue to delete.
     *
     * @return void
     */
    private function _deleteQueue($url)
    {
        $manager = $this->_getQueueManager();

        try {
            $result = $manager->deleteQueue($url);
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }

        $this->_output(
            "Queue has been deleted. It may take up to 60 seconds for the " .
            "queue list to reflect this change.\n"
        );
    }

    // }}}
    // {{{ _send()

    /**
     * Sends a message from STDIN to the specified queue
     *
     * @param string $url the queue URL of the queue to which the message is
     *                    sent.
     *
     * @return void
     */
    private function _send($url)
    {
        $queue = new Services_Amazon_SQS_Queue($url,
            $this->_accessKey, $this->_secretAccessKey);

        $messageBody = file_get_contents('php://stdin');

        try {
            $messageId = $queue->send($messageBody);
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }

        $this->_output($messageId . "\n");
    }

    // }}}
    // {{{ _receive()

    /**
     * Receives a message from the specified queue and displays its body on
     * STDOUT
     *
     * @param string  $url     the queue URL of the queue from which to receive
     *                         the message.
     * @param boolean $delete  optional. Whether or not to delete the message
     *                         after receiving it. Defaults to false.
     * @param integer $timeout optional. The visibility timeout of the received
     *                         message. Defaults to 30 seconds.
     *
     * @return void
     */
    private function _receive($url, $delete = false, $timeout = 30)
    {
        $queue = new Services_Amazon_SQS_Queue($url,
            $this->_accessKey, $this->_secretAccessKey);

        try {
            $messages = $queue->receive(1, $timeout);
            if (count($messages) > 0) {
                if ($delete) {
                    $queue->delete($messages[0]['handle']);
                }
                // display exactly as returned; do not run through _output().
                echo $messages[0]['body'];
            }
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }
    }

    // }}}
    // {{{ _getUiXml()

    /**
     * Gets the command-line user-interface definition XML file
     *
     * @return string the user-interface definition for this command-line
     *                application.
     */
    private function _getUiXml()
    {
        $dir = '@data-dir@' . DIRECTORY_SEPARATOR
            . '@package-name@' . DIRECTORY_SEPARATOR . 'data';

        if ($dir[0] == '@') {
            $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . 'data';
        }

        return $dir . DIRECTORY_SEPARATOR . 'cli.xml';
    }

    // }}}
    // {{{ _loadConfig()

    /**
     * Loads the configuration file for the SQS command-line application
     *
     * If required config values are missing, the command-line script
     * terminiates.
     *
     * @return void
     */
    private function _loadConfig()
    {
        if ($this->_configFilename == '') {
            $configFile  = PEAR_Config::singleton()->get('cfg_dir');
            $configFile .= DIRECTORY_SEPARATOR . '@package-name@';
            $configFile .= DIRECTORY_SEPARATOR . 'sqs.ini';
        } else {
            $configFile = $this->_configFilename;
        }

        if (!file_exists($configFile)) {
            $this->_output(
                "Configuration file \xe2\x80\x98" . $configFile .
                "\xe2\x80\x99 was not found.\n",
                STDERR
            );
            exit(1);
        }

        if (!is_readable($configFile)) {
            $this->_output(
                "Configuration file \xe2\x80\x98" . $configFile .
                "\xe2\x80\x99 is not readable.\n",
                STDERR
            );
            exit(1);
        }

        $handler = set_error_handler(
            array(__CLASS__, '_handleParseError'),
            E_WARNING
        );

        $config = parse_ini_file($configFile);
        restore_error_handler(E_WARNING);

        if ($config === false) {
            $this->_output(
                "Could not parse configuration file \xe2\x80\x98" .
                $configFile . "\xe2\x80\x99.\n",
                STDERR
            );
            exit(1);
        }

        if (array_key_exists('access_key', $config)) {
            $this->_accessKey = $config['access_key'];
        }
        if (array_key_exists('secret_access_key', $config)) {
            $this->_secretAccessKey = $config['secret_access_key'];
        }

        // make sure access key is set
        if ($this->_accessKey == '') {
            $this->_output(
                "Access key id is missing from configuration file. Please " .
                "set your Amazon Web Services access key id in the " .
                "\xe2\x80\x98access_key\xe2\x80\x99 field in the file " .
                "\xe2\x80\x98" . $configFile . "\xe2\x80\x99.\n",
                STDERR
            );
            exit(1);
        }

        // make sure secret access key is set
        if ($this->_secretAccessKey == '') {
            $this->_output(
                "Secret access key id is missing from configuration file. " .
                "Please set your Amazon Web Services secret access key id " .
                "in the \xe2\x80\x98secret_access_key\xe2\x80\x99 field in " .
                "the file \xe2\x80\x98" . $configFile . "\xe2\x80\x99.\n",
                STDERR
            );
            exit(1);
        }
    }

    // }}}
    // {{{ _getQueueManager()

    /**
     * Gets the queue manager used to perform queue management in this
     * application
     *
     * @return Services_Amazon_SQS_QueueManager the queue manager.
     */
    private function _getQueueManager()
    {
        if ($this->_manager === null) {
            $this->_manager = new Services_Amazon_SQS_QueueManager(
                $this->_accessKey, $this->_secretAccessKey);
        }

        return $this->_manager;
    }

    // }}}
    // {{{ _handleException()

    /**
     * Handles exceptions
     *
     * @param Services_Amazon_SQS_Exception $e the exception to handle.
     *
     * @return void
     */
    private function _handleException(Services_Amazon_SQS_Exception $e)
    {
        $this->_output($e->getMessage() . "\n", STDERR, false);
        exit(1);
    }

    // }}}
    // {{{ _handleParseError()

    /**
     * Handles errors when parsing the configuration file
     *
     * @param integer $errno  the error level of the error.
     * @param string  $errstr the error message.
     *
     * @return void
     */
    private static function _handleParseError($errno, $errstr)
    {
        $exp     = '/Error parsing (.*?) on line (\d+)/';
        $matches = array();
        if (preg_match($exp, $errstr, $matches) === 1) {

            $this->_output(
                "Error parsing configuration file \xe2\x80\x98" . $matches[1] .
                "\xe2\x80\x99 on line " . $matches[2] . "\n",
                STDERR
            );

        } else {
            $this->_output(trim($errstr) . "\n", STDERR, false);
        }

        exit(1);
    }

    // }}}
    // {{{ _getConsoleEncoding()

    /**
     * Detects the character encoding of the console
     *
     * @return string the character encoding of the console. If the character
     *                encoding could not be detected, ISO-8859-1 is returned.
     */
    private function _getConsoleEncoding()
    {
        // cache the results
        static $encoding = null;

        if ($encoding === null) {

            if (function_exists('nl_langinfo') && is_callable('nl_langinfo')) {

                // system supports nl_langinfo, use it
                $encoding = nl_langinfo(CODESET);

            } else {

                // try to detect encoding from locale identifier
                $lcCtype  = null;
                $lcAll    = setlocale(LC_ALL, '0');
                $lcAllExp = explode(';', $lcAll);

                // get LC_CTYPE from the locale info if it exists
                if (count($lcAllExp) === 1) {
                    $lcCtype = reset($lcAllExp);
                } else {
                    foreach ($lcAllExp as $lc) {
                        if (strncmp($lc, 'LC_CTYPE', 8) === 0) {
                            $lcCtype = $lc;
                            break;
                        }
                    }
                }

                // handle locales like 'en_US.UTF-8'
                if ($lcCtype !== null) {
                    $lcCtypeExp = explode('.', $lcCtype, 2);
                    if (count($lcCtypeExp) === 2) {
                        $encoding = $lcCtypeExp[1];
                    }
                }

            }

            // we did not detect an encoding, use ISO-8859-1
            if ($encoding === null) {
                $encoding = 'ISO-8859-1';
            }

        }

        return $encoding;
    }

    // }}}
    // {{{ _output()

    /**
     * Outputs text to an output stream
     *
     * @param string   $text   the text to output.
     * @param resource $stream optional. The output stream to use. If not
     *                         specified, STDOUT is used.
     * @param boolean  $utf8   optional. Whether or not the input text is UTF-8
     *                         encoded. If the console does not support UTF-8,
     *                         text is decoded to ASCII before display.
     *
     * @return void
     */
    private function _output($text, $stream = null, $utf8 = true)
    {
        if ($stream === null) {
            $stream = STDOUT;
        }

        // UTF-8 characters used in strings
        static $search = array(
            "\xe2\x80\x98",
            "\xe2\x80\x99"
        );

        // ASCII equivalents
        static $replace = array(
            "'",
            "'"
        );

        // get system newlines
        $text = str_replace("\n", PHP_EOL, $text);

        // convert to ASCII if input is UTF-8 and encoding is not UTF-8
        $encoding = $this->_getConsoleEncoding();
        if ($utf8 && preg_match('/^utf-?8$/i', $encoding) === 0) {
            $text = str_replace($search, $replace, $text);
        }

        // send text to stream
        fwrite($stream, $text);
    }

    // }}}
}

?>
