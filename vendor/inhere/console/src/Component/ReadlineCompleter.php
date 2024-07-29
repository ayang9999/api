<?php declare(strict_types=1);
/**
 * The file is part of inhere/console
 *
 * @author   https://github.com/inhere
 * @homepage https://github.com/inhere/php-console
 * @license  https://github.com/inhere/php-console/blob/master/LICENSE
 */

namespace Inhere\Console\Component;

use Toolkit\Cli\Util\Readline;
use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * Class ReadlineCompleter
 *
 * @package Inhere\Console\Component
 */
class ReadlineCompleter extends AbstractObj
{
    /**
     * @var string
     */
    private string $historyFile = '';

    /**
     * @var int
     */
    private int $historySize = 1024;

    /**
     * @var callable
     */
    private $completer;

    /**
     * @return bool
     */
    public function isSupported(): bool
    {
        return Readline::isSupported();
    }

    /**
     * @param callable $completer
     *
     * @return ReadlineCompleter
     */
    public function setCompleter(callable $completer): static
    {
        $this->completer = $completer;
        return $this;
    }

    /**
     * @return array
     */
    public function listHistory(): array
    {
        return Readline::listHistory();
    }

    /**
     * @return bool
     */
    public function loadHistory(): bool
    {
        if ($this->historyFile) {
            return Readline::loadHistory($this->historyFile);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function dumpHistory(): bool
    {
        if ($this->historyFile) {
            return Readline::dumpHistory($this->historyFile);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHistoryFile(): string
    {
        return $this->historyFile;
    }

    /**
     * @param string $historyFile
     */
    public function setHistoryFile(string $historyFile): void
    {
        $this->historyFile = $historyFile;
    }

    /**
     * @return int
     */
    public function getHistorySize(): int
    {
        return $this->historySize;
    }

    /**
     * @param int $historySize
     */
    public function setHistorySize(int $historySize): void
    {
        $this->historySize = $historySize;
    }

    /**
     * @return callable
     */
    public function getCompleter(): callable
    {
        return $this->completer;
    }
}
