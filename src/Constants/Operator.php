<?php

declare(strict_types=1);
/**
 * This file is part of the codemagpie/simple-query-builder package.
 *
 * (c) CodeMagpie Lyf <https://github.com/codemagpie>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CodeMagpie\SimpleQueryBuilder\Constants;

class Operator
{
    public const IN = 'in';
    public const NOT_IN = 'notIn';
    public const BETWEEN = 'between';
    public const OPEN_BETWEEN = 'openBetween';
    public const LEFT_OPEN_BETWEEN = 'rightOpenBetween';
    public const RIGHT_OPEN_BETWEEN = 'rightOpenBetween';
    public const LIKE = 'like';
    public const LEFT_LIKE = 'leftLike';
    public const RIGHT_LIKE = 'rightLike';
    public const EQUAL = 'equal';
    public const NOT_EQUAL = 'notEqual';
    public const LESS = 'less';
    public const LESS_OR_EQUAL = 'lessOrEqual';
    public const GREAT = 'great';
    public const GREAT_OR_EQUAL = 'greatOrEqual';
    public const IS_NULL = 'isNull';
    public const IS_NOT_NULL = 'isNotNull';

    public const ALIAS_MAP = [
        '=' => self::EQUAL,
        '!=' => self::NOT_EQUAL,
        '<>' => self::NOT_EQUAL,
        '<' => self::LESS,
        '>' => self::GREAT,
        '<=' => self::LESS_OR_EQUAL,
        '>=' => self::GREAT_OR_EQUAL,
    ];
}
