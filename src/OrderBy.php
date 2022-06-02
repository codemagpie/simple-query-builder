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
namespace CodeMagpie\SimpleQueryBuilder;

use CodeMagpie\SimpleQueryBuilder\Constants\Direction;
use CodeMagpie\SimpleQueryBuilder\Exception\SimpleQueryException;
use CodeMagpie\Utils\Utils;

class OrderBy
{
    protected string $field;

    protected string $direction;

    public function __construct(string $field, string $direction)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    protected function validate(): void
    {
        if (in_array($this->direction, Utils::getEnumValues(Direction::class), true)) {
            throw new SimpleQueryException(sprintf('illegal direction %s', $this->direction));
        }
    }
}
