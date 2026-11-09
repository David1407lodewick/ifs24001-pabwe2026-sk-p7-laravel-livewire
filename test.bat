@echo off
set PAO_DISABLE=1
php -r "$flag = (extension_loaded('pcov') || extension_loaded('xdebug')) ? '--coverage-text' : '--no-coverage'; passthru(PHP_BINARY . ' vendor/bin/phpunit ' . $flag . ' %*', $code); exit($code);"
