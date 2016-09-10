<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$run     = new Whoops\Run;
$handler = new PrettyPageHandler;
// Set the title of the error page:
$handler->setPageTitle("Whoops! There was a problem.");

$run->pushHandler($handler);

// Register the handler with PHP, and you're set!
$run->register();
