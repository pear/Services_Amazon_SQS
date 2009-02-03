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
require_once 'Console/Getopt.php';

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
 * @copyright 2008 Mike Brittain, 2008 silverorange
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 * @link      http://aws.amazon.com/sqs/
 * @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf
 */
class Services_Amazon_SQS_CLI
{
    // {{{ private properties

    /**
     * The command-line parser for this script
     *
     * @var Console_CommandLine
     */
    private $_parser = null;

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

    /**
     * Whether or not to show usage statistics for the curent command
     *
     * @var boolean
     */
    private $_showStats = false;

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
        $result = $this->_parse();

        $this->_setOptions($result['options']);
        $this->_loadConfig();
        $this->_runCommand($result['command'], $result['argument']);

        if ($this->_showStats) {
            $this->_displayStats();
        }
    }

    // }}}
    // {{{ _parse()

    /**
     * Parses the command line of this application
     *
     * This gets global options, the command to run and the argument for the
     * command (if it exists).
     *
     * @return array an associative array containing the following fields:
     *               - <kbd>array  options</kbd>  - an array of options suitable
     *                                              to pass to the _setOptions()
     *                                              method.
     *               - <kbd>string command</kbd>  - the command to run.
     *               - <kbd>string argument</kbd> - the argument for the
     *                                              command. If there is no
     *                                              argument, this will be an
     *                                              empty string.
     */
    private function _parse()
    {
        $result = array(
            'options'  => array(),
            'command'  => '',
            'argument' => ''
        );

        $argv  = $_SERVER['argv'];
        $short = 'c:';
        $long  = array('config=');
        $opts  = Console_Getopt::getopt($argv, $short, $long);
        if (PEAR::isError($opts)) {
            $message = $opts->getMessage();
            $message = preg_replace('/^Console_Getopt: /', '', $message);
            echo PHP_EOL;
            printf('ERROR: %s. Try `sqs help\'.', $message);
            echo PHP_EOL, PHP_EOL;
            exit(1);
        }

        // parse options
        $options = $opts[0];
        foreach ($options as $option) {
            switch ($option[0]) {
            case 'c':
            case '--config':
                $result['options']['config'] = $option[1];
                break;
            }
        }

        // get command
        $commands = $opts[1];
        if (count($commands) === 0) {
            $command = 'help';
        } else {
            $command = array_shift($commands);
        }

        $validCommands = array(
            'create',
            'delete',
            'help',
            'list',
            'send',
            'receive',
            'version'
        );

        if (!in_array($command, $validCommands)) {
            printf(
                'Command `%s\' is not valid. Try `sqs help\'.',
                $command
            );

            echo PHP_EOL;
            exit(1);
        }

        $result['command'] = $command;

        if (count($commands) > 0) {
            $result['argument'] = $commands[0];
        }

        return $result;
    }

    // }}}
    // {{{ _setOptions()

    /**
     * Sets the options
     *
     * Options are set from an array of named values. Available option names
     * are:
     *
     * - <kbd>string  config</kbd> - the path to the configuration file to use.
     *
     * @param array $options optional. An associative array of containing the
     *                       options to use.
     *
     * @return void
     */
    private function _setOptions(array $options)
    {
        if (array_key_exists('config', $options)) {
            $this->_configFilename = strval($options['config']);
        }
    }

    // }}}
    // {{{ _runCommand()

    /**
     * Runs the specified command for this application
     *
     * @param string $command  the command to run. If the command is not a
     *                         valid command, no action is taken.
     * @param string $argument optional. The argument for the command. If no
     *                         argument is specified, an empty string is used.
     *
     * @return void
     */
    private function _runCommand($command, $argument = '')
    {
        switch ($command) {
        case 'create':
            if ($argument == '') {
                echo 'No queue name specified.', PHP_EOL;
                exit(1);
            }
            $this->_createQueue($argument);
            break;

        case 'delete':
            if ($argument == '') {
                echo 'No queue URI specified.', PHP_EOL;
                exit(1);
            }
            $this->_deleteQueue($argument);
            break;

        case 'help':
            $this->_displayHelp($argument);
            break;

        case 'list':
            $this->_listQueues($argument);
            break;

        case 'send':
            if ($argument == '') {
                echo 'No queue URI specified.', PHP_EOL;
                exit(1);
            }
            $this->_send($argument);
            break;

        case 'receive':
            if ($argument == '') {
                echo 'No queue URI specified.', PHP_EOL;
                exit(1);
            }
            $this->_receive($argument);
            break;

        case 'version':
            $this->_displayVersion();
            break;
        }
    }

    // }}}
    // {{{ _displayHelp()

    /**
     * Displays the appropriate help section
     *
     * @param string $argument the argument passed to the <i>help</i> command.
     *                         Appropriate help is displayed depending on the
     *                         argument value.
     *
     * @return void
     */
    private function _displayHelp($argument)
    {
        if ($argument === '') {
            $this->_help();
        } else {
            switch ($argument) {
            case 'create':
                $this->_helpCreate();
                break;
            case 'delete':
                $this->_helpDelete();
                break;
            case 'list':
                $this->_helpList();
                break;
            case 'send':
                $this->_helpSend();
                break;
            case 'receive':
                $this->_helpReceive();
                break;
            case 'version':
                $this->_helpVersion();
                break;
            default:
                printf(
                    'Command `%s\' is not valid. Try `sqs help\'.',
                    $argument
                );

                echo PHP_EOL;
                exit(1);
            }
        }
    }

    // }}}
    // {{{ _help()

    /**
     * Displays general command-line usage help
     *
     * @return void
     */
    private function _help()
    {
        echo
            'A command-line interface to Amazon\'s Simple Queue Service.',
            PHP_EOL,
            PHP_EOL,
            'Usage:', PHP_EOL,
            '  sqs [options] command [args]', PHP_EOL,
            PHP_EOL,
            'Commands:', PHP_EOL,
            '  create   Creates a new queue with the specified name.', PHP_EOL,
            '  delete   Deletes an existing queue by the specified URI.',
            PHP_EOL,
            '  list     Lists avaiable queues.', PHP_EOL,
            '  send     Sends a message to the specified queue.', PHP_EOL,
            '  receive  Receives a message from the specified queue.', PHP_EOL,
            '  version  Displays version information.', PHP_EOL,
            PHP_EOL,
            'Options:', PHP_EOL,
            '  -c, --config=file  Find configuration in `file\'.', PHP_EOL,
            PHP_EOL,
            'Type `sqs help <command>\' to get the help for the specified ',
            'command.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpCreate()

    /**
     * Displays help for the <i>create</i> command
     *
     * @return void
     */
    private function _helpCreate()
    {
        echo
            'sqs create <queue-name>', PHP_EOL,
            PHP_EOL,
            'Creates a new queue with the specified name. The queue may take ',
            'up to 60 seconds to become available.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpDelete()

    /**
     * Displays help for the <i>delete</i> command
     *
     * @return void
     */
    private function _helpDelete()
    {
        echo
            'sqs delete <queue-uri>', PHP_EOL,
            PHP_EOL,
            'Deletes a queue with the specified URI. The queue may take up ',
            'to 60 seconds to become unavailable.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpList()

    /**
     * Displays help for the <i>list</i> command
     *
     * @return void
     */
    private function _helpList()
    {
        echo
            'sqs list [prefix]', PHP_EOL,
            PHP_EOL,
            'Lists available queues. If a prefix is specified, only queues ',
            'beginning with the specified prefix are listed.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpSend()

    /**
     * Displays help for the <i>send</i> command
     *
     * @return void
     */
    private function _helpSend()
    {
        echo
            'sqs send <queue-uri>', PHP_EOL,
            PHP_EOL,
            'Sends input from STDIN to the specified queue. The resulting ',
            'message identifier is displayed on STDOUT.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpReceive()

    /**
     * Displays help for the <i>receive</i> command
     *
     * @return void
     */
    private function _helpReceive()
    {
        echo
            'sqs receive [options] <queue-uri>', PHP_EOL,
            PHP_EOL,
            'Receives a message from the specified queue. The message body ',
            'is displayed on STDOUT. If no message is received, nothing is ',
            'displayed on STDOUT.', PHP_EOL,
            PHP_EOL,
            'Options:', PHP_EOL,
            '  -d, --delete         Deletes the message after receiving it.',
            PHP_EOL,
            '  -t, --timeout=value  Sets the visibility timeout for the ',
            'received message', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _helpVersion()

    /**
     * Displays help for the <i>version</i> command
     *
     * @return void
     */
    private function _helpVersion()
    {
        echo
            'sqs version', PHP_EOL,
            PHP_EOL,
            'Displays version information and exits.', PHP_EOL,
            PHP_EOL;
    }

    // }}}
    // {{{ _listQueues()

    /**
     * Lists SQS queues
     *
     * @param string $prefix optional. Only list queues whose name begins with
     *                       the given prefix. If not specified, all queues are
     *                       returned.
     *
     * @return void
     */
    private function _listQueues($prefix = '')
    {
        $manager = $this->_getQueueManager();

        try {
            $queues = $manager->listQueues($prefix);
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }

        if (count($queues) === 0) {
            echo 'No queues available.', PHP_EOL;
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

            $format = '%-55s  %-10s  %-10s' . PHP_EOL;

            // display header
            printf($format, '', 'ITEMS', 'VIS.');
            printf($format, 'QUEUE NAME', '(APPROX.)', 'TIMEOUT');

            foreach ($rows as $row) {
                printf($format, $row['name'], $row['number'], $row['timeout']);
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

        echo 'New queue has been added. It may take up to 60 seconds for the ',
            'new queue to appear in the list of queues.', PHP_EOL;
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

        echo 'Queue has been deleted. It may take up to 60 seconds for the ',
            'queue list to reflect this change.', PHP_EOL;
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

        echo $messageId, PHP_EOL;
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
                echo $messages[0]['body'];
            }
        } catch (Services_Amazon_SQS_Exception $e) {
            $this->_handleException($e);
        }
    }

    // }}}
    // {{{ _displayVersion()

    /**
     * Displays version information for this script
     *
     * @return void
     */
    private function _displayVersion()
    {
        echo $_SERVER['SCRIPT_NAME'], ' version ', $this->_getVersion(),
            PHP_EOL;
    }

    // }}}
    // {{{ _getVersion()

    /**
     * Gets the version of this application
     *
     * The version identifier is derived from the installed package version.
     *
     * @return string the version of this application.
     */
    private function _getVersion()
    {
        $version = '@package-version@';

        if ($version[0] == '@') {
            $version = 'CVS';
        }

        return $version;
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
            echo PHP_EOL;
            printf(
                'ERROR: Configuration file `%s\' was not found.',
                $configFile
            );

            echo PHP_EOL, PHP_EOL;
            exit(1);
        }

        if (!is_readable($configFile)) {
            echo PHP_EOL;
            printf(
                'ERROR: Configuration file `%s\' is not readable.',
                $configFile
            );

            echo PHP_EOL, PHP_EOL;
            exit(1);
        }

        $handler = set_error_handler(
            array(__CLASS__, '_handleParseError'),
            E_WARNING
        );

        $config = parse_ini_file($configFile);
        restore_error_handler(E_WARNING);

        if ($config === false) {
            echo PHP_EOL;
            printf(
                'ERROR: Could not parse configuration file `%s\'.',
                $configFile
            );

            echo PHP_EOL, PHP_EOL;
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
            echo PHP_EOL;
            printf(
                'ERROR: Access key id is missing from configuration file. ' .
                'Please set your Amazon Web Services access key id in the ' .
                '`access_key\' field in the file `%s\'.',
                $configFile
            );

            echo PHP_EOL, PHP_EOL;
            exit(1);
        }

        // make sure secret access key is set
        if ($this->_secretAccessKey == '') {
            echo PHP_EOL;
            printf(
                'ERROR: Secret access key id is missing from ' .
                'configuration file. Please set your Amazon Web Services ' .
                'secret access key id in the `secret_access_key\' field in ' .
                'the file `%s\'.',
                $configFile
            );

            echo PHP_EOL, PHP_EOL;
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
        echo PHP_EOL, 'ERROR: ', $e->getMessage(), PHP_EOL, PHP_EOL;
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
            echo PHP_EOL;
            printf(
                'ERROR: Error parsing configuration file `%s\' on line %s.',
                $matches[1],
                $matches[2]
            );

            echo PHP_EOL, PHP_EOL;
        } else {
            echo PHP_EOL, 'ERROR: ', trim($errstr), PHP_EOL, PHP_EOL;
        }

        exit(1);
    }

    // }}}
}

?>
