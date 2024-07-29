<?php declare(strict_types=1);
/**
 * The file is part of inhere/console
 *
 * @author   https://github.com/inhere
 * @homepage https://github.com/inhere/php-console
 * @license  https://github.com/inhere/php-console/blob/master/LICENSE
 */

namespace Inhere\Console\Decorate;

use Closure;
use Generator;
use Inhere\Console\Component\Formatter\HelpPanel;
use Inhere\Console\Component\Formatter\MultiList;
use Inhere\Console\Component\Formatter\Panel;
use Inhere\Console\Component\Formatter\Section;
use Inhere\Console\Component\Formatter\SingleList;
use Inhere\Console\Component\Formatter\Table;
use Inhere\Console\Component\Formatter\Title;
use Inhere\Console\Util\Interact;
use Inhere\Console\Util\Show;
use LogicException;
use Toolkit\Cli\Style;
use function method_exists;
use function sprintf;
use function substr;

/**
 * Trait StyledOutputAwareTrait
 *
 * @package Inhere\Console\Decorate
 *
 * @method int info($messages, $quit = false)
 * @method int note($messages, $quit = false)
 * @method int notice($messages, $quit = false)
 * @method int success($messages, $quit = false)
 * @method int primary($messages, $quit = false)
 * @method int warning($messages, $quit = false)
 * @method int danger($messages, $quit = false)
 * @method int error($messages, $quit = false)
 *
 * @method int liteInfo($messages, $quit = false)
 * @method int liteNote($messages, $quit = false)
 * @method int liteNotice($messages, $quit = false)
 * @method int liteSuccess($messages, $quit = false)
 * @method int litePrimary($messages, $quit = false)
 * @method int liteWarning($messages, $quit = false)
 * @method int liteDanger($messages, $quit = false)
 * @method int liteError($messages, $quit = false)
 *
 * @method padding(array $data, string $title = null, array $opts = [])
 *
 * @method splitLine(string $title, string $char = '-', int $width = 0)
 * @method spinner($msg = '', $ended = false)
 * @method loading($msg = 'Loading ', $ended = false)
 * @method pending($msg = 'Pending ', $ended = false)
 * @method pointing($msg = 'handling ', $ended = false)
 *
 * @method Generator counterTxt($msg = 'Pending ', $ended = false)
 *
 * @method bool confirm(string $question, bool $default = true)
 * @method bool unConfirm(string $question, bool $default = true)
 * @method string select(string $description, $options, $default = null, bool $allowExit = true, array $opts = [])
 * @method array checkbox(string $description, $options, $default = null, bool $allowExit = true)
 * @method string ask(string $question, string $default = '', Closure $validator = null)
 * @method string askPassword(string $prompt = 'Enter Password:')
 * @see Interact
 */
trait StyledOutputAwareTrait
{
    /**
     * @param string $text
     * @param string $tag all tag please {@see Color::STYLES}
     *
     * @return int
     */
    public function colored(string $text, string $tag = 'info'): int
    {
        return $this->writeln(sprintf('<%s>%s</%s>', $tag, $text, $tag));
    }

    /**
     * @param array|mixed $messages
     * @param string      $type
     * @param string      $style
     * @param bool $quit
     *
     * @return int
     */
    public function block(mixed $messages, string $type = 'MESSAGE', string $style = Style::NORMAL, bool $quit = false): int
    {
        return Show::block($messages, $type, $style, $quit);
    }

    /**
     * @param array|mixed $messages
     * @param string      $type
     * @param string      $style
     * @param bool $quit
     *
     * @return int
     */
    public function liteBlock(mixed $messages, string $type = 'MESSAGE', string $style = Style::NORMAL, bool $quit = false): int
    {
        return Show::liteBlock($messages, $type, $style, $quit);
    }

    /**
     * @param string $title
     * @param array  $opts
     */
    public function title(string $title, array $opts = []): void
    {
        Title::show($title, $opts);
    }

    /**
     * @param string       $title
     * @param array|string $body The section body message
     * @param array        $opts
     */
    public function section(string $title, array|string $body, array $opts = []): void
    {
        Section::show($title, $body, $opts);
    }

    /**
     * @param array|mixed $data
     * @param string      $title
     * @param array       $opts
     */
    public function aList(mixed $data, string $title = 'Information', array $opts = []): void
    {
        SingleList::show($data, $title, $opts);
    }

    /**
     * @param array $data
     * @param array $opts
     */
    public function multiList(array $data, array $opts = []): void
    {
        MultiList::show($data, $opts);
    }

    /**
     * @param array $data
     * @param array $opts
     */
    public function mList(array $data, array $opts = []): void
    {
        MultiList::show($data, $opts);
    }

    /**
     * @param array $config
     */
    public function helpPanel(array $config): void
    {
        HelpPanel::show($config);
    }

    /**
     * @param array  $data
     * @param string $title
     * @param array  $opts
     */
    public function panel(array $data, string $title = 'Information panel', array $opts = []): void
    {
        Panel::show($data, $title, $opts);
    }

    /**
     * @param array  $data
     * @param string $title
     * @param array  $opts
     */
    public function table(array $data, string $title = 'Data Table', array $opts = []): void
    {
        Table::show($data, $title, $opts);
    }

    /**
     * @param int    $total
     * @param string $msg
     * @param string $doneMsg
     *
     * @return Generator
     */
    public function progressTxt(int $total, string $msg, string $doneMsg = ''): Generator
    {
        return Show::progressTxt($total, $msg, $doneMsg);
    }

    /**
     * @param integer $total
     * @param array $opts
     *
     * @return Generator
     * @see Show::progressBar()
     */
    public function progressBar(int $total, array $opts = []): Generator
    {
        return Show::progressBar($total, $opts);
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return int
     * @throws LogicException
     */
    public function __call(string $method, array $args = [])
    {
        $map = Show::getBlockMethods(false);

        if (isset($map[$method])) {
            $msg   = $args[0];
            $quit  = $args[1] ?? false;
            $style = $map[$method];

            if (str_starts_with($method, 'lite')) {
                $type = substr($method, 4);
                return Show::liteBlock($msg, $type === 'Primary' ? 'IMPORTANT' : $type, $style, $quit);
            }

            return Show::block($msg, $style === 'primary' ? 'IMPORTANT' : $style, $style, $quit);
        }

        if (method_exists(Show::class, $method)) {
            return Show::$method(...$args);
        }

        // interact methods
        if (method_exists(Interact::class, $method)) {
            return Interact::$method(...$args);
        }

        throw new LogicException("Call a not exists method: $method of the " . static::class);
    }
}
