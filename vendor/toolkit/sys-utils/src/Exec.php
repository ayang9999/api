<?php declare(strict_types=1);
/**
 * This file is part of toolkit/sys-utils.
 *
 * @author   https://github.com/inhere
 * @link     https://github.com/php-toolkit/sys-utils
 * @license  MIT
 */

namespace Toolkit\Sys;

use RuntimeException;
use Toolkit\Sys\Proc\ProcWrapper;
use function chdir;
use function exec;
use function function_exists;
use function getcwd;
use function implode;
use function ob_get_clean;
use function ob_start;
use function pclose;
use function popen;
use function shell_exec;
use function stream_get_contents;
use function system;
use function trim;

/**
 * Class Exec
 *
 * @package Toolkit\Sys
 */
class Exec
{
    /**
     * @param string $command
     * @param string $workDir
     * @param bool   $outAsString
     *
     * @return array{int, string|array}
     */
    public static function exec(string $command, string $workDir = '', bool $outAsString = true): array
    {
        $curDir = '';
        if ($workDir) {
            $curDir = getcwd();
            chdir($workDir);
        }

        exec($command, $output, $status);

        // fix: revert workdir after run end.
        if ($curDir) {
            chdir($curDir);
        }

        return [$status, $outAsString ? implode("\n", $output) : $output];
    }

    /**
     * Exec an command and get output. use popen()
     *
     * @param string $command
     * @param string $workDir
     *
     * @return string
     */
    public static function pexec(string $command, string $workDir = ''): string
    {
        $output = $curDir = '';
        if ($workDir) {
            $curDir = getcwd();
            chdir($workDir);
        }

        // popen
        $proc = popen($command, 'rb');
        if (false !== $proc) {
            $output = stream_get_contents($proc);
            pclose($proc);
        }

        // fix: revert workdir after run end.
        if ($curDir) {
            chdir($curDir);
        }

        return $output;
    }

    /**
     * run a command. it is support windows
     *
     * @param string $command
     * @param string $cwd
     *
     * @return array [$code, $output, $error]
     * @throws RuntimeException
     */
    public static function run(string $command, string $cwd = ''): array
    {
        return ProcWrapper::runCmd($command, $cwd);
    }

    /**
     * @param string $command
     * @param string $workDir
     * @param bool   $allReturn
     *
     * @return array
     */
    public static function system(string $command, string $workDir = '', bool $allReturn = false): array
    {
        $curDir = '';
        if ($workDir) {
            $curDir = getcwd();
            chdir($workDir);
        }

        if ($allReturn) {
            ob_start();
            system($command, $status);
            $output = ob_get_clean();
        } else {
            // only last line message
            $output = system($command, $status);
        }

        // fix: revert workdir after run end.
        if ($curDir) {
            chdir($curDir);
        }

        return [$status, $output];
    }

    /**
     * @param string $command
     * @param string $workDir
     *
     * @return string|null
     */
    public static function shellExec(string $command, string $workDir = ''): ?string
    {
        $curDir = '';
        if ($workDir) {
            $curDir = getcwd();
            chdir($workDir);
        }

        $ret = shell_exec($command);
        // fix: revert workdir after run end.
        if ($curDir) {
            chdir($curDir);
        }

        return $ret;
    }

    /**
     * run a command in background
     *
     * @param string $cmd
     */
    public static function bgExec(string $cmd): void
    {
        self::inBackground($cmd);
    }

    /**
     * run a command in background
     *
     * @param string $cmd
     */
    public static function inBackground(string $cmd): void
    {
        if (SysEnv::isWindows()) {
            pclose(popen('start /B ' . $cmd, 'r'));
        } else {
            exec($cmd . ' > /dev/null &');
        }
    }

    /**
     * Quick exec an command and return output
     *
     * @param string $command
     * @param string $cwd
     *
     * @return string
     */
    public static function getOutput(string $command, string $cwd = ''): string
    {
        return self::auto($command, false, $cwd);
    }

    /**
     * Method to execute a command in the sys
     *
     * Will try uses :
     * 1. system
     * 2. passthru - will report error on windows
     * 3. exec
     * 4. shell_exec
     * 5. popen
     *
     * @param string $command
     * @param bool   $getStatus
     * @param string $cwd
     *
     * @return string|array{status:int, output:string}
     */
    public static function auto(string $command, bool $getStatus = true, string $cwd = ''): array|string
    {
        $status = 1;
        $curDir = '';

        if ($cwd) {
            $curDir = getcwd();
            chdir($cwd);
        }

        // system
        if (function_exists('system')) {
            ob_start();
            system($command, $status);
            $output = ob_get_clean();

            // exec
        } elseif (function_exists('exec')) {
            exec($command, $output, $status);
            $output = implode("\n", $output);

            // shell_exec
        } elseif (function_exists('shell_exec')) {
            $output = shell_exec($command);
        } else {
            // popen
            $proc = popen($command, 'rb');
            if (false !== $proc) {
                $status = 0;
                $output = stream_get_contents($proc);
            } else {
                $status = -1;
                $output = 'Command execution not possible on this system';
            }
        }

        // fix: revert workdir after run end.
        if ($curDir) {
            chdir($curDir);
        }

        if ($getStatus) {
            return [
                'output' => trim($output),
                'status' => $status
            ];
        }
        return trim($output);
    }

    /**
     * @param string $command
     * @param string $logfile
     * @param string $user
     *
     * @return mixed
     * @throws RuntimeException
     */
    public static function execWithSudo(string $command, string $logfile = '', string $user = ''): mixed
    {
        // If should run as another user, we must be on *nix and must have sudo privileges.
        $suDo = '';
        if ($user && SysEnv::isUnix() && SysEnv::isRoot()) {
            $suDo = "sudo -u $user";
        }

        // Start execution. Run in foreground (will block).
        $logfile = $logfile ?: SysEnv::getNullDevice();

        // Start execution. Run in foreground (will block).
        exec("$suDo $command 1>> \"$logfile\" 2>&1", $dummy, $retVal);

        if ($retVal !== 0) {
            throw new RuntimeException("command exited with status '$retVal'.");
        }

        return $dummy;
    }
}
