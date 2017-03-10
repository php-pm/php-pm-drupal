<?php
/**
 * @file
 *   Alternate bootstrap file for starting up the app under PHP-FPM.
 */
 
set_time_limit(0);
ini_set('html_errors', 0);

print PHP_EOL . 'Running ' . __FILE__ . PHP_EOL;

function includeIfExists($file)
{
	if (file_exists($file)) {
		return include $file;
	}
}

if (
    (!$loader = includeIfExists(__DIR__.'/../autoload.php'))
    && (!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php'))
    && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))
    && (!$loader = includeIfExists(__DIR__.'/vendor/autoload.php'))
) {
	$msg = 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
			'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
			'php composer.phar install'. PHP_EOL;
	die($msg);
}

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

final class AppKernel extends Kernel
{
	public function registerBundles()
	{
		$bundles = [
		// we'll put stuff here later
		];
		return $bundles;
	}
	public function registerContainerConfiguration(LoaderInterface $loader)
	{

	}
	public function getLogDir()
	{
		return __DIR__.'/../var/log';
	}
	public function getCacheDir()
	{
		return __DIR__.'/../var/cache/'.$this->getEnvironment();
	}
}

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;
use PHPPM\Commands\StartCommand;
use PHPPM\Commands\StatusCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\BufferingLogger;

// Debug::enable();
// ErrorHandler::stackErrors();
// ErrorHandler::register(new ErrorHandler(new BufferingLogger()));

$input = new ArgvInput(['vendor/bin/ppm', 'start', '/var/www/html']);

$app = new Application('PHP-PM');
$app->add(new StartCommand);
$app->add(new StatusCommand);

// This setup with php-fcgi SAPI doesn't support php://stderr or php://stdout.
$output = new StreamOutput(fopen('php://output', 'w'));

$app->run($input, $output);
