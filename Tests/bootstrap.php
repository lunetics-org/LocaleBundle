<?php

/*
 * This file is part of the LocaleBundle package.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!is_file($autoloadFile = __DIR__.'/../vendor/autoload.php') && !is_file($autoloadFile = __DIR__.'/../../../../../autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}
require $autoloadFile;