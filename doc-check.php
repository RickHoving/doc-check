#!/usr/bin/env php
<?php
/**
 * This File is the part of the Doc-Check app
 *
 * @see the link to the documentation
 */

// Ensure we have the autloader loaded and working

foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

use Symfony\Component\Console\Application;
use \DocCheck\Command\DocCheckCommand;

$command = new DocCheckCommand();

$application = new Application();
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();