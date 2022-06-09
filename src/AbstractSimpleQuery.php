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

use Closure;
use CodeMagpie\SimpleQueryBuilder\BindTrait\BindElasticaQueryBuilderTrait;
use CodeMagpie\SimpleQueryBuilder\BindTrait\BindHyperfQueryBuilderTrait;
use CodeMagpie\SimpleQueryBuilder\Constants\Boolean;
use CodeMagpie\SimpleQueryBuilder\Constants\Direction;
use CodeMagpie\SimpleQueryBuilder\Constants\Operator;
use CodeMagpie\SimpleQueryBuilder\Exception\SimpleQueryException;

/**
 * @method AbstractSimpleQuery whereIn(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereNotIn(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereBetween(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereOpenBetween(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereLeftOpenBetween(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereRightOpenBetween(string $field, array $values, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereLike(string $field, string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereLeftLike(string $field, string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereRightLike(string $field, string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereEqual(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereNotEqual(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereLess(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereLessOrEqual(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereGreat(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereGreatOrEqual(string $field, float|int|string $value, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereIsNull(string $field, string $boolean = Boolean::AND)
 * @method AbstractSimpleQuery whereIsNotNull(string $field, string $boolean = Boolean::AND)
 *
 * @method AbstractSimpleQuery orWhereIn(string $field, array $values)
 * @method AbstractSimpleQuery orWhereNotIn(string $field, array $values)
 * @method AbstractSimpleQuery orWhereBetween(string $field, array $values)
 * @method AbstractSimpleQuery orWhereOpenBetween(string $field, array $values)
 * @method AbstractSimpleQuery orWhereLeftOpenBetween(string $field, array $values)
 * @method AbstractSimpleQuery orWhereRightOpenBetween(string $field, array $values)
 * @method AbstractSimpleQuery orWhereLike(string $field, string $value)
 * @method AbstractSimpleQuery orWhereLeftLike(string $field, string $value)
 * @method AbstractSimpleQuery orWhereRightLike(string $field, string $value)
 * @method AbstractSimpleQuery orWhereEqual(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereNotEqual(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereLess(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereLessOrEqual(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereGreat(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereGreatOrEqual(string $field, float|int|string $value)
 * @method AbstractSimpleQuery orWhereIsNull(string $field)
 * @method AbstractSimpleQuery orWhereIsNotNull(string $field)
 */
abstract class AbstractSimpleQuery
{
    use BindElasticaQueryBuilderTrait;
    use BindHyperfQueryBuilderTrait;

    /**
     * @var string[] allow the query field, ['*'] is allow all
     */
    protected array $fields = [];

    /**
     * @var OrderBy[]
     */
    private array $orderBys = [];

    /**
     * @var Where[]
     */
    private array $wheres = [];

    private ?Pagination $pagination = null;

    public function __call($name, $arguments): AbstractSimpleQuery
    {
        $field = current($arguments);
        $value = next($arguments);
        if (strpos($name, 'orWhere') !== false) {
            $operator = str_replace('orWhere', '', $name);
            $boolean = Boolean::OR;
        } elseif (strpos($name, 'where') !== false) {
            $operator = str_replace('where', '', $name);
            if (in_array(lcfirst($operator), [Operator::IS_NULL, Operator::IS_NOT_NULL], true)) {
                $boolean = count($arguments) > 1 ? end($arguments) : Boolean::AND;
                $value = null;
            } elseif (count($arguments) <= 2) {
                $boolean = Boolean::AND;
            } else {
                $boolean = end($arguments);
            }
        } else {
            throw new SimpleQueryException(sprintf('illegal method %s', $name));
        }
        return $this->where($field, lcfirst($operator), $value, $boolean);
    }

    /**
     * @param int|string $page
     * @param int|string $perPage
     */
    public function forPage($page, $perPage = 15): AbstractSimpleQuery
    {
        $this->pagination = new Pagination((int) $page, (int) $perPage);
        return $this;
    }

    /**
     * @return static
     */
    public static function build(): AbstractSimpleQuery
    {
        return new static();
    }

    public function where(string $field, string $operator, $value = null, string $boolean = Boolean::AND): AbstractSimpleQuery
    {
        if (! is_object($value) && current($this->getFields()) !== '*' && ! in_array($field, $this->getFields())) {
            throw new SimpleQueryException(sprintf('illegal query field %s', $field));
        }
        if (! $this->wheres) {
            $boolean = Boolean::AND;
        }
        $this->wheres[] = new Where($field, $operator, $value, $boolean);
        return $this;
    }

    public function orderBy(string $field, string $direction = Direction::ASC): AbstractSimpleQuery
    {
        $this->orderBys[] = new OrderBy($field, $direction);
        return $this;
    }

    public function orderByDesc(string $field): AbstractSimpleQuery
    {
        return $this->orderBy($field, Direction::DESC);
    }

    public function orWhere(string $field, $operator = null, $value = null): AbstractSimpleQuery
    {
        return $this->where($field, $operator, $value, Boolean::OR);
    }

    public function addNestedWhere(Closure $closure, string $boolean = Boolean::AND): AbstractSimpleQuery
    {
        $newQuery = static::build();
        $closure($newQuery);
        return $this->where('', '', $newQuery, $boolean);
    }

    public function addNestedOrWhere(Closure $closure): AbstractSimpleQuery
    {
        $newQuery = static::build();
        $closure($newQuery);
        return $this->where('', '', $newQuery, Boolean::OR);
    }

    /**
     * @return Where[]
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @return OrderBy[]
     */
    public function getOrderBys(): array
    {
        return $this->orderBys;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }
}
