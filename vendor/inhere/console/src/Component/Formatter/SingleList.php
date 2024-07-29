<?php declare(strict_types=1);
/**
 * The file is part of inhere/console
 *
 * @author   https://github.com/inhere
 * @homepage https://github.com/inhere/php-console
 * @license  https://github.com/inhere/php-console/blob/master/LICENSE
 */

namespace Inhere\Console\Component\Formatter;

use Inhere\Console\Component\MessageFormatter;
use Inhere\Console\Console;
use Inhere\Console\Util\FormatUtil;
use Toolkit\Cli\Color\ColorTag;
use Toolkit\Stdlib\Str;
use function array_merge;
use function trim;
use const PHP_EOL;

/**
 * Class SingleList - Format and render a single list
 *
 * @package Inhere\Console\Component\Formatter
 */
class SingleList extends MessageFormatter
{
    /**
     * Format and render a single list
     *
     * ```php
     * $title = 'list title';
     * $data = [
     *      'name'  => 'value text',
     *      'name2' => 'value text 2',
     * ];
     * ```
     *
     * @param array|mixed $data
     * @param string      $title
     * @param array       $opts More {@see FormatUtil::spliceKeyValue()}
     *
     * @return int|string
     */
    public static function show(mixed $data, string $title = 'Information', array $opts = []): int|string
    {
        $string = '';
        $opts   = array_merge([
            'leftChar'     => '  ',
            // 'sepChar' => '  ',
            'keyStyle'     => 'info',
            'keyMinWidth'  => 8,
            'titleStyle'   => 'comment',
            'ucFirst'      => false,
            'returned'     => false,
            'ucTitleWords' => true,
            'lastNewline'  => true,
        ], $opts);

        // title
        if ($title) {
            $title  = $opts['ucTitleWords'] ? Str::ucwords(trim($title)) : $title;
            $string .= ColorTag::wrap($title, $opts['titleStyle']) . PHP_EOL;
        }

        // handle item list
        $string .= FormatUtil::spliceKeyValue((array)$data, $opts);

        // return formatted string.
        if ($opts['returned']) {
            return $string;
        }

        return Console::write($string, $opts['lastNewline']);
    }
}
