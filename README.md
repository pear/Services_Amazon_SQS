# Services_Amazon_SQS #
This package provides an object-oriented interface to the [Amazon Simple Queue
Service (SQS)](http://aws.amazon.com/sqs/). Included are client libraries and
a command-line script for managing queues. You will need a set of web-service
keys from Amazon Web Services that have SQS enabled. You can [sign up for an
account on Amazon](https://aws-portal.amazon.com/gp/aws/developer/registration/index.html).

Note: Although this package has no cost, Amazon's Web services are not free to
use. You will be billed by Amazon for your use of SQS.

This package is derived with permission from the [simple-aws
package](http://code.google.com/p/simple-aws/) written by Mike Brittain.

This package requires PHP 5.2.1. On Red Hat flavored distributions, the
"php-xml" package must also be installed.

There are two main ways to use this package. Firstly, it provides an API that
may be used to manage queues, and to add and remove messages from queues in
PHP scripts. The [Services_Amazon_SQS_QueueManager](http://pear.php.net/package/Services_Amazon_SQS/docs/latest/Services_Amazon_SQS/Services_Amazon_SQS_QueueManager.html)
and [Services_Amazon_SQS_Queue](http://pear.php.net/package/Services_Amazon_SQS/docs/latest/Services_Amazon_SQS/Services_Amazon_SQS_Queue.html)
classes provide these methods.

Secondly, a command-line application for managing SQS queues is provided. This
is installed by PEAR as "sqs". Running this command produces the following
output:

```
A command-line interface to Amazon's Simple Queue Service (SQS).

Usage:
  /usr/bin/sqs [options]
  /usr/bin/sqs [options] <command> [options] [args]

Options:
  -c config, --config=config Find configuration in <config>

Commands:
  create   Creates a new queue with the specified name. The queue may
           take up to 60 seconds to become available.
  delete   Deletes an existing queue by the specified URI. The queue
           may take up to 60 seconds to become unavailable.
  help     Displays an overview of available options and commands, or
           detailed help for a specific command.
  list     Lists available queues. If a prefix is specified, only
           queues beginning with the specified prefix are listed.
  send     Sends input from STDIN to the specified queue. The
           resulting message identifier is displayed on STDOUT.
  receive  Receives a message from the specified queue. The message
           body is displayed on STDOUT. If no message is received,
           nothing is displayed on STDOUT.
  version  Displays version information and exits.
```

[Services_Amazon_SQS](http://pear.php.net/package/Services_Amazon_SQS) has been
migrated from [PEAR SVN](https://svn.php.net/repository/pear/packages/Services_Amazon_SQS).

## Documentation ##
* [Detailed API Documentation](http://pear.php.net/package/Services_Amazon_SQS/docs/latest/)

## Bugs and Issues ##
Please report all new issues via the [PEAR bug tracker](http://pear.php.net/bugs/search.php?cmd=display&package_name[]=Services_Amazon_SQS).

Please submit pull requests for your bug reports!

## Testing ##
To test, run either
$ phpunit tests/
  or
$ pear run-tests -r

## Building ##
To build, simply
$ pear package

## Installing ##
To install from scratch
$ pear install package.xml

To upgrade
$ pear upgrade -f package.xml
