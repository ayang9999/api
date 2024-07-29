<?php declare(strict_types=1);
/**
 * This file is part of toolkit/sys-utils.
 *
 * @author   https://github.com/inhere
 * @link     https://github.com/php-toolkit/sys-utils
 * @license  MIT
 */

namespace Toolkit\Sys\Traits;

use RuntimeException;
use function defined;
use function extension_loaded;
use function function_exists;
use function get_defined_constants;
use function get_loaded_extensions;
use function in_array;

/**
 * Trait PhpEnvTrait
 *
 * @package Toolkit\Sys\Traits
 */
trait PhpEnvTrait
{
    /**************************************************************************
     * php env
     *************************************************************************/

    /**
     * Get PHP version
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return defined('HHVM_VERSION') ? HHVM_VERSION : PHP_VERSION;
    }

    /**
     * isEmbed
     *
     * @return  boolean
     */
    public static function isEmbed(): bool
    {
        return 'embed' === PHP_SAPI;
    }

    /**
     * @return bool
     */
    public static function isCgi(): bool
    {
        return stripos(PHP_SAPI, 'cgi') !== false;   #  cgi环境
    }

    /**
     * is Cli
     *
     * @return  boolean
     */
    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * is Build In Server
     * run server use like: `php -S 127.0.0.1:8085`
     *
     * @return  boolean
     */
    public static function isBuiltInServer(): bool
    {
        return PHP_SAPI === 'cli-server';
    }

    /**
     * @return bool
     */
    public static function isDevServer(): bool
    {
        return PHP_SAPI === 'cli-server';
    }

    /**
     * isWeb
     *
     * @return  boolean
     */
    public static function isWeb(): bool
    {
        return in_array(PHP_SAPI, [
            'apache',
            'cgi',
            'fast-cgi',
            'cgi-fcgi',
            'fpm-fcgi',
            'srv',
            'cli-server'
        ], true);
    }

    /**
     * isHHVM
     *
     * @return  boolean
     */
    public static function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    /**
     * isPHP
     *
     * @return  boolean
     */
    public static function isPHP(): bool
    {
        return !static::isHHVM();
    }

    /**
     * setStrict
     *
     * @return  void
     */
    public static function setStrict(): void
    {
        error_reporting(32767);
    }

    /**
     * setMuted
     *
     * @return  void
     */
    public static function setMuted(): void
    {
        \error_reporting(0);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function hasExtension(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return boolean
     */
    public static function hasXDebug(): bool
    {
        return static::isPHP() && extension_loaded('xdebug');
    }

    /**
     * @return bool
     */
    public static function hasPcntl(): bool
    {
        return function_exists('pcntl_fork');
    }

    /**
     * @return bool
     */
    public static function hasPosix(): bool
    {
        return function_exists('posix_kill');
    }

    /**
     * @param            $name
     * @param bool|false $throwException
     *
     * @return bool
     * @throws RuntimeException
     */
    public static function extIsLoaded(string $name, bool $throwException): bool
    {
        $result = extension_loaded($name);

        if (!$result && $throwException) {
            throw new RuntimeException("Extension [$name] is not loaded.");
        }

        return $result;
    }

    /**
     * 检查多个扩展加载情况
     *
     * @param array $extensions
     *
     * @return array|bool
     */
    public static function checkExtList(array $extensions = []): bool|array
    {
        $allTotal = [];

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $allTotal['no'][] = $extension;
            } else {
                $allTotal['yes'][] = $extension;
            }
        }

        return $allTotal;
    }

    /**
     * 返回加载的扩展
     *
     * @param bool $zend_extensions
     *
     * @return array
     */
    public static function getLoadedExtension(bool $zend_extensions): array
    {
        return get_loaded_extensions($zend_extensions);
    }

    /**
     * @return array
     */
    public static function getUserConstants(): array
    {
        $const = get_defined_constants(true);

        return $const['user'] ?? [];
    }
}
