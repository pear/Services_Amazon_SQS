<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This is the package.xml generator for Services_Amazon_SQS
 *
 * PHP version 5
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
 * @link      http://pear.php.net/package/Services_Amazon_SQS
 */

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '0.2.0';
$api_state       = 'alpha';

$release_version = '0.2.0';
$release_state   = 'alpha';
$release_notes   =
    "Second PEAR release. Updated to use Amazon Signature Version 2. See " .
    "http://developer.amazonwebservices.com/connect/entry.jspa?externalID=1928";

$description =
    "This package provides an object-oriented interface to the Amazon Simple " .
    "Queue Service (SQS). Included are client libraries and a command-line " .
    "script for managing queues. You will need a set of web-service keys " .
    "from Amazon Web Services that have SQS enabled. You can sign up for an " .
    "account at: " .
    "https://aws-portal.amazon.com/gp/aws/developer/registration/index.html." .
    "\n\n" .
    "Note: Although this package has no cost, Amazon's Web services are not " .
    "free to use. You will be billed by Amazon for your use of SQS." .
    "\n\n" .
    "This package is derived with permission from the simple-aws package " .
    "available at http://code.google.com/p/simple-aws/ and written by Mike " .
    "Brittain." .
    "\n\n" .
    "This package requires PHP 5.2.1. On Red Hat flavored distributions, the " .
    "'php-xml' package must also be installed." .
    "\n\n" .
    "There are two main ways to use this package. Firstly, it provides an " .
    "API that may be used to manage queues, and to add and remove messages " .
    "from queues in PHP scripts. The Services_Amazon_SQS_QueueManager and " .
    "Services_Amazon_SQS_Queue classes provide these methods." .
    "\n\n" .
    "Secondly, a command-line application for managing SQS queues is " .
    "provided. This is installed by PEAR as `sqs'. Running this command " .
    "produces the following output:" .
    "\n\n" .
    "A command-line interface to Amazon's Simple Queue Service." .
    "\n\n" .
    "Usage:\n" .
    " sqs [options] command [args]" .
    "\n\n" .
    "Commands:\n" .
    " create Creates a new queue with the specified name.\n" .
    " delete Deletes an existing queue by the specified URI.\n" .
    " list Lists avaiable queues.\n" .
    " version Displays version information." .
    "\n\n" .
    "Options:\n" .
    " -c, --config=file Find configuration in `file'." .
    "\n\n" .
    "Type `sqs help <command>' to get the help for the specified command.";

$package = new PEAR_PackageFileManager2();

$package->setOptions(array(
    'filelistgenerator'       => 'cvs',
    'simpleoutput'            => true,
    'baseinstalldir'          => '/',
    'packagedirectory'        => './',
    'dir_roles'               => array(
        'Services'            => 'php',
        'Services/Amazon'     => 'php',
        'Services/Amazon/SQS' => 'php',
        'tests'               => 'test'
    ),
    'exceptions'              => array(
        'scripts/sqs'         => 'script',
        'scripts/sqs.bat'     => 'script',
        'cfg/sqs.ini'         => 'cfg',
        'cfg/sqs-win.ini'     => 'cfg'
    ),
    'ignore'                  => array(
        'package.php',
        '*.tgz'
    ),
    'installexceptions'       => array(
        'scripts/sqs'         => '/',
        'scripts/sqs.bat'     => '/',
        'cfg/sqs.ini'         => '/',
        'cfg/sqs-win.ini'     => '/'
    )
));

$package->setPackage('Services_Amazon_SQS');
$package->setSummary('PHP API and tools for Amazon SQS');
$package->setDescription($description);
$package->setChannel('pear.php.net');
$package->setPackageType('php');
$package->setLicense('Apache License 2.0',
    'http://www.apache.org/licenses/LICENSE-2.0');

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer('lead', 'gauthierm', 'Mike Gauthier',
    'mike@silverorange.com');

$package->addMaintainer('lead', 'mikebrittain', 'Mike Brittain',
    'mike@mikebrittain.com');

$package->addReplacement('scripts/sqs', 'pear-config',
    '@php-bin@', 'php_bin');

$package->addReplacement('scripts/sqs.bat', 'pear-config',
    '@php-bin@', 'php_bin');

$package->addReplacement('scripts/sqs.bat', 'pear-config',
    '@bin-dir@', 'bin_dir');

$package->addReplacement('scripts/sqs.bat', 'pear-config',
    '@php-dir@', 'php_dir');

$package->addReplacement('Services/Amazon/SQS/CLI.php', 'package-info',
    '@package-version@', 'version');

$package->addReplacement('Services/Amazon/SQS/CLI.php', 'package-info',
    '@package-name@', 'name');

$package->addReplacement('Services/Amazon/SQS.php', 'package-info',
    '@api-version@', 'api-version');

$package->addReplacement('Services/Amazon/SQS.php', 'package-info',
    '@name@', 'name');

$package->addWindowsEol('scripts/sqs.bat');
$package->addWindowsEol('cfg/sqs-win.ini');

$package->setPhpDep('5.2.1');

$package->addPackageDepWithChannel('required', 'PEAR',
    'pear.php.net');

$package->addPackageDepWithChannel('required', 'Console_Getopt',
    'pear.php.net');

$package->addPackageDepWithChannel('required', 'Crypt_HMAC2',
    'pear.php.net', '0.2.1');

$package->addPackageDepWithChannel('required', 'Net_URL2',
    'pear.php.net', '0.2.0');

$package->addPackageDepWithChannel('required', 'HTTP_Request2',
    'pear.php.net', '0.1.0');

$package->setPearInstallerDep('1.7.0');
$package->generateContents();

// windows release
$package->addRelease();
$package->setOsInstallCondition('windows');
$package->addIgnoreToRelease('cfg/sqs.ini');
$package->addInstallAs('scripts/sqs', 'sqs');
$package->addInstallAs('scripts/sqs.bat', 'sqs.bat');
$package->addInstallAs('cfg/sqs-win.ini', 'sqs.ini');

// *nix release
$package->addRelease();
$package->addIgnoreToRelease('scripts/sqs.bat');
$package->addIgnoreToRelease('cfg/sqs-win.ini');
$package->addInstallAs('scripts/sqs', 'sqs');
$package->addInstallAs('cfg/sqs.ini', 'sqs.ini');

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

?>
