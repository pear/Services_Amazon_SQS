@echo off

REM vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:

REM Command-line interface for managing Amazon Simple Queue Service (SQS) queues
REM
REM PHP version 5
REM
REM LICENSE:
REM
REM Copyright 2008 Mike Brittain, silverorange
REM
REM Licensed under the Apache License, Version 2.0 (the "License");
REM you may not use this file except in compliance with the License.
REM You may obtain a copy of the License at
REM
REM   http://www.apache.org/licenses/LICENSE-2.0
REM
REM Unless required by applicable law or agreed to in writing, software
REM distributed under the License is distributed on an "AS IS" BASIS,
REM WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
REM See the License for the specific language governing permissions and
REM limitations under the License.
REM
REM @category  Services
REM @package   Services_Amazon_SQS
REM @author    Mike Brittain <mike@mikebrittain.com>
REM @author    Michael Gauthier <mike@silverorange.com>
REM @copyright 2008 Mike Brittain, 2008 silverorange
REM @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
REM @version   CVS: $Id: sqs.bat,v 1.1 2008-12-11 15:42:56 gauthierm Exp $
REM @link      http://pear.php.net/package/Services_Amazon_SQS
REM @link      http://aws.amazon.com/sqs/
REM @link      http://s3.amazonaws.com/awsdocs/SQS/20080101/sqs-dg-20080101.pdf

"@php-bin@" -d auto_append_file="" -d auto_prepend_file="" -d include_path="@php-dir@" "@bin-dir@\sqs" %*
