<?php declare(strict_types=1);
/**
 * This file is part of toolkit/cli-utils.
 *
 * @link     https://github.com/php-toolkit/cli-utils
 * @author   https://github.com/inhere
 * @license  MIT
 */

use Toolkit\Cli\Util\Download;

require dirname(__DIR__) . '/test/bootstrap.php';

$url  = 'http://no2.php.net/distributions/php-7.2.5.tar.bz2';
$down = Download::create($url);

$type = 'bar';

$down->setShowType($type);
$down->start();
