<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Send message tests for the Services_Amazon_SQS package.
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
 * Services_Amazon_SQS test base class
 */
require_once dirname(__FILE__) . '/TestCase.php';

/**
 * Send message tests for Services_Amazon_SQS
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
class Services_Amazon_SQS_SendMessageTestCase extends
    Services_Amazon_SQS_TestCase
{
    // {{{ testSendMessage()

    /**
     * @group message
     */
    public function testSendMessage()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<SendMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <SendMessageResult>
    <MD5OfMessageBody>8b25734299a8efa7eeb74bf261bdc72d</MD5OfMessageBody>
    <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
  </SendMessageResult>
  <ResponseMetadata>
    <RequestId>ead9e7a4-10c2-4c59-8a4e-4381bab50565</RequestId>
  </ResponseMetadata>
</SendMessageResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers);

        $messageId = $this->queue->send('Services_Amazon_SQS Unit Test');

        $this->assertEquals('90b160de-132b-45c6-afea-4679b27a485d', $messageId);
    }

    // }}}
    // {{{ testSendMessageInvalidLength()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_InvalidMessageException
     */
    public function testSendMessageInvalidLength()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidParameterValue</Code>
    <Message>Value for parameter MessageBody is invalid. Reason: Message body must be shorter than 8192 bytes.</Message>
    <Detail/>
  </Error>
  <RequestId>99b031bc-732d-405f-a277-06b79151ab92</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // Intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        // {{{ data
        $data = <<<DATA
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vivamus
luctus placerat erat. Curabitur euismod arcu id eros. Maecenas
accumsan pede in orci consectetuer tristique. Ut nec felis non nunc
euismod ornare. Ut dui eros, euismod sit amet, condimentum nec, varius
sit amet, leo. Cras ipsum. Cum sociis natoque penatibus et magnis dis
parturient montes, nascetur ridiculus mus. Duis porta. Donec neque
magna, interdum eu, dictum non, viverra a, est. Praesent vel neque.
Mauris vitae nunc. Praesent commodo eros eget eros. Nunc congue.
Maecenas quis massa sed ante dapibus vestibulum. Sed at dui. Duis
mauris nulla, sodales vel, tincidunt ut, sodales ac, tellus. Donec
ullamcorper. Maecenas sollicitudin sagittis leo. Cum sociis natoque
penatibus et magnis dis parturient montes, nascetur ridiculus mus.
Phasellus leo.
Maecenas posuere. Morbi massa. Maecenas dolor felis, dignissim nec,
vulputate quis, auctor vel, lectus. Sed libero. Cras mi. Proin
placerat libero vitae dolor. Donec non nunc vel nisi sodales
facilisis. Ut a tortor vitae neque eleifend rhoncus. Suspendisse
vehicula, lectus vitae elementum porta, diam eros scelerisque sem, et
accumsan massa orci a urna. Vivamus posuere, massa sit amet sagittis
pulvinar, leo erat congue metus, eu congue lacus tortor in mauris.
Vestibulum dapibus lorem in turpis. Curabitur aliquet gravida purus.
Integer quis felis at velit varius laoreet. Pellentesque aliquam
consectetuer lectus.
Nunc at sapien. Cras faucibus dignissim dolor. Sed id diam ac augue
aliquet convallis. Proin feugiat, lectus elementum venenatis aliquet,
nibh lectus porttitor tortor, sit amet aliquet erat sapien eu nulla.
Morbi sagittis libero sed tellus. Sed purus turpis, sollicitudin vel,
dapibus eu, vehicula vel, dui. Nullam fringilla tellus in lorem.
Integer dolor. Mauris interdum tristique neque. Nullam ac magna.
Nam vel eros id enim dignissim sagittis. Suspendisse pede. Aenean et
lacus at sapien molestie consequat. Vestibulum sapien. Aliquam erat
volutpat. Duis vel felis ac risus consequat tristique. Curabitur porta
enim. Phasellus auctor consectetuer justo. Sed rhoncus congue turpis.
Nam consequat massa ac elit. Nam aliquam. Nam velit.
Aliquam condimentum vestibulum risus. Vivamus id nunc ut ante
consequat hendrerit. Donec sit amet ante ut nisi vulputate malesuada.
Aliquam id nisi at justo ornare imperdiet. Curabitur ac felis eu pede
posuere pretium. Cras sed justo et enim pretium mattis. Maecenas nec
metus. Fusce sed odio. Vivamus aliquam dictum nunc. Nullam aliquam
magna eu massa. Etiam sit amet lacus. Duis velit mauris, ullamcorper
sit amet, facilisis sit amet, ultricies id, neque. Aenean ut arcu in
magna suscipit ultricies. Nulla mauris. Morbi sed purus. Maecenas vel
augue id lorem tempor volutpat. Sed congue porttitor mi. Mauris
fringilla magna quis enim. Sed eleifend, nibh ut ornare lobortis,
augue mauris egestas sapien, sit amet rutrum risus sem non felis.
Pellentesque ipsum augue, ornare et, lobortis sit amet, aliquet ac,
nulla.
Aliquam sit amet massa. Morbi nec odio ut diam ornare pellentesque.
Etiam iaculis purus quis neque. Cras gravida velit at ante. Curabitur
a justo vitae nulla molestie molestie. Pellentesque sed urna vel enim
rutrum tempor. Sed lectus tortor, consectetuer ac, aliquet ac, commodo
id, elit. Sed eleifend turpis. Maecenas turpis dui, vulputate in,
euismod id, dignissim eget, massa. Integer id mauris. Mauris a diam.
Curabitur scelerisque, arcu ac aliquet eleifend, nunc elit egestas
libero, ut luctus ipsum enim in risus. Quisque a pede. Sed euismod
ligula vel lectus. Aliquam erat volutpat. Curabitur quis ligula.
Vivamus justo odio, scelerisque ut, feugiat id, dapibus quis, nibh.
Aliquam et ante vitae risus mattis iaculis. Aenean pretium ligula et
magna viverra vehicula. Proin felis.
Proin malesuada fermentum sem. Vivamus suscipit. Aenean et risus.
Quisque vel urna. In hac habitasse platea dictumst. Ut vel quam eu
odio semper congue. Aenean pulvinar, quam eget faucibus consequat,
purus est tristique tellus, quis iaculis lectus nibh a neque. Cras
diam. Vestibulum est enim, pellentesque vitae, sagittis id, semper
non, sem. Maecenas iaculis.
Cum sociis natoque penatibus et magnis dis parturient montes,
nascetur ridiculus mus. Nam quam massa, ultricies a, tristique quis,
luctus ut, enim. Ut hendrerit tellus sit amet diam. Mauris eget tellus
sit amet tortor lacinia mattis. Praesent eros enim, iaculis id,
ultricies sed, ultrices id, eros. Donec libero nibh, accumsan id,
vehicula vel, bibendum vitae, est. Morbi ac velit vitae nibh pharetra
semper. Aliquam eros turpis, porta sit amet, bibendum quis, porta
vitae, ante. Fusce ac turpis nec nulla euismod fermentum. Integer
imperdiet lorem consectetuer libero. Quisque luctus. Nam erat augue,
ornare vitae, accumsan vitae, molestie id, massa. Integer cursus.
Suspendisse libero.
Nulla sed magna. Nulla suscipit, urna a varius aliquam, mi nulla
posuere eros, fermentum consectetuer dui orci vel ipsum. Fusce varius
erat in libero. Lorem ipsum dolor sit amet, consectetuer adipiscing
elit. Morbi lacinia. Etiam accumsan ultrices erat. Integer eu mi. In
vel purus. Integer porttitor. Fusce pede enim, venenatis sit amet,
varius rutrum, convallis eu, orci. Phasellus dictum lacinia ante.
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut non
tellus eget metus rhoncus condimentum. Praesent consequat augue eu
erat. Donec accumsan, turpis a ultricies venenatis, neque tortor porta
velit, sed sagittis velit sapien a tellus. Pellentesque eget diam.
Phasellus luctus sagittis sem. Pellentesque ac risus. Sed ac velit.
Maecenas sed justo. Sed ut sem. Morbi elit massa, pretium eget,
aliquet sed, ornare ut, ligula. Etiam vitae mi eget nisi facilisis
molestie. Phasellus varius mattis mauris. Suspendisse ut nunc.
Praesent nec arcu. Nullam in lacus. In vitae est.
In hac habitasse platea dictumst. Mauris ultrices. Nunc vulputate,
augue quis imperdiet vehicula, libero orci cursus lectus, et mollis
velit lorem eget lorem. Ut id dui a purus porttitor varius. Nulla
neque nisl, vehicula et, venenatis at, pretium in, lectus.
Pellentesque at lacus et sem dictum laoreet. Vestibulum quis mauris.
Morbi fringilla cursus diam. Phasellus id neque. Aenean magna sem,
facilisis non, luctus vitae, egestas a, sem. Ut lobortis urna pulvinar
diam. Fusce in velit eget nibh mollis vulputate. In lorem ligula,
commodo ut, tempor non, egestas quis, leo. Nullam sit amet purus.
Integer pellentesque sagittis orci. Quisque neque lectus, porta
euismod, tristique et, dictum ac, arcu. Mauris congue consequat dui.
Cum sociis natoque penatibus et magnis dis parturient montes, nascetur
ridiculus mus. Nunc ut nibh nec nibh facilisis elementum. Maecenas vel
metus.
Etiam id orci at nulla consequat volutpat. Vestibulum vel nulla et
leo sodales fringilla. Phasellus eu nisl et quam fringilla molestie.
Aenean eget metus. Donec diam arcu, dignissim vitae, fermentum dictum,
accumsan ac, purus. Proin tempor aliquam lorem. Nullam facilisis odio
a ante. Aliquam dui. Nam id leo et massa fermentum tempus. In
pellentesque elit ut metus. Fusce mollis est in libero. Donec orci
nulla, scelerisque non, elementum id, semper accumsan, ante. Phasellus
nec risus porta nunc molestie vulputate. Etiam justo sem, sagittis
lobortis, laoreet scelerisque, ornare nec, arcu. Lorem ipsum dolor sit
amet, consectetuer adipiscing elit. Nunc laoreet sem mattis massa.
Cras porttitor lectus vel metus. Integer tellus felis, sodales sed,
pharetra vitae, sodales sit amet, nulla. Maecenas vulputate vestibulum
nisl.
Donec sollicitudin sollicitudin ante. Curabitur sed ipsum. Nunc odio
eros, consequat vitae, condimentum luctus, auctor at, tortor. Morbi
nisl. Vivamus gravida metus vel justo. Aliquam eget quam sed nunc
faucibus vestibulum. Nullam in erat. Etiam volutpat nunc quis turpis.
Pellentesque blandit. Praesent sagittis urna in orci facilisis varius.
Donec ultricies. Fusce lobortis placerat urna. Quisque mattis, enim id
venenatis mollis, elit metus ultrices quam, id commodo libero nisi sit
amet sapien.
Quisque sagittis porta odio. Maecenas ac tellus. Etiam ornare nibh eu
arcu. Quisque euismod facilisis lorem. In mauris. Vivamus urna. Sed
nisi. Curabitur convallis ultrices arcu. Nullam sit amet mi. Donec at
arcu. Etiam hendrerit. Phasellus ullamcorper, orci nec fringilla
convallis, felis purus tempor eros, tempor venenatis massa velit sit
amet velit. Class aptent taciti sociosqu ad litora torquent per
conubia nostra, per inceptos himenaeos. Donec libero.
DATA;
        // }}}
        $messageId = $this->queue->send($data);
    }

    // }}}
    // {{{ testSendMessageInvalidContent()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_InvalidMessageException
     */
    public function testSendMessageInvalidContent()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>InvalidMessageContents</Code>
    <Message>An invalid binary character was found in the message body, the set of allowed characters is #x9 | #xA | #xD | [#x20-#xD7FF] | [#xE000-#xFFFD] | [#x10000-#x10FFFF]</Message>
    <Detail/>
  </Error>
  <RequestId>f8e3e05c-4a09-4d6f-b530-9a2d78e90dca</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // Intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $messageId = $this->queue->send("\x0e");
    }

    // }}}
    // {{{ testSendMessageInvalidQueue()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_InvalidQueueException
     */
    public function testSendMessageInvalidQueue()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<ErrorResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <Error>
    <Type>Sender</Type>
    <Code>AWS.SimpleQueueService.NonExistentQueue</Code>
    <Message>The specified queue does not exist for this wsdl version.</Message>
    <Detail/>
  </Error>
  <RequestId>05714b4b-7359-4527-9bd1-c9aaacb4a2ad</RequestId>
</ErrorResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Cneonction'        => 'close', // Intentional misspelling
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers, 'HTTP/1.1 400 Bad Request');

        $queue = new Services_Amazon_SQS_Queue(
            'http://queue.amazonaws.com/this-queue-does-not-exist',
            '123456789ABCDEFGHIJK',
            'abcdefghijklmnopqrstuzwxyz/ABCDEFGHIJKLM',
            $this->request
        );

        $messageId = $queue->send('Hello, World!');
    }

    // }}}
    // {{{ testSendMessageInvalidChecksum()

    /**
     * @group message
     * @expectedException Services_Amazon_SQS_ChecksumException
     */
    public function testSendMessageInvalidChecksum()
    {
        // {{{ response body
        $body = <<<XML
<?xml version="1.0"?>
<SendMessageResponse xmlns="http://queue.amazonaws.com/doc/2009-02-01/">
  <SendMessageResult>
    <MD5OfMessageBody>6b25734299a8efa7eeb74bf261bdc72a</MD5OfMessageBody>
    <MessageId>90b160de-132b-45c6-afea-4679b27a485d</MessageId>
  </SendMessageResult>
  <ResponseMetadata>
    <RequestId>ead9e7a4-10c2-4c59-8a4e-4381bab50565</RequestId>
  </ResponseMetadata>
</SendMessageResponse>
XML;

        $body = $this->formatXml($body);
        // }}}
        // {{{ response headers
        $headers = array(
            'Content-Type'      => 'text/xml',
            'Transfer-Encoding' => 'chunked',
            'Date'              => 'Sun, 18 Jan 2009 17:34:20 GMT',
            'Server'            => 'AWS Simple Queue Service'
        );
        // }}}
        $this->addHttpResponse($body, $headers);

        $messageId = $this->queue->send('Services_Amazon_SQS Unit Test');
    }

    // }}}
}

?>
