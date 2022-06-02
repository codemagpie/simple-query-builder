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

use CodeMagpie\SimpleQueryBuilder\Constants\Boolean;
use CodeMagpie\SimpleQueryBuilder\Constants\Operator;
use CodeMagpie\SimpleQueryBuilder\Exception\SimpleQueryException;
use CodeMagpie\Utils\Utils;

class Where
{
    protected string $name;

    protected string $operator = '';

    protected string $boolean;

    /**
     * @var AbstractSimpleQuery|mixed
     */
    protected $value;

    public function __construct(string $name, string $operator, $value, string $boolean)
    {
        $operator = Operator::ALIAS_MAP[$operator] ?? $operator;
        $this->name = $name;
        $this->operator = $operator;
        $this->value = $value;
        $this->boolean = $boolean;
        $this->validate();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getBoolean(): string
    {
        return $this->boolean;
    }

    /**
     * @return AbstractSimpleQuery|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    protected function validate(): void
    {
        if (! in_array($this->boolean, Utils::getEnumValues(Boolean::class), true)) {
            throw new SimpleQueryException(sprintf('illegal where boolean %s', $this->boolean));
        }
        if (! is_object($this->value) && ! in_array($this->operator, Utils::getEnumValues(Operator::class), true)) {
            throw new SimpleQueryException(sprintf('illegal where operator %s', $this->operator));
        }
        if (is_object($this->value) && ! $this->value instanceof AbstractSimpleQuery) {
            throw new SimpleQueryException(sprintf('nested query,the value must be instanceof   %s', AbstractSimpleQuery::class));
        }
    }
}
