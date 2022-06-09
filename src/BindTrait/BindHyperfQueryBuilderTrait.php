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
namespace CodeMagpie\SimpleQueryBuilder\BindTrait;

use CodeMagpie\SimpleQueryBuilder\OrderBy;
use CodeMagpie\SimpleQueryBuilder\Pagination;
use CodeMagpie\SimpleQueryBuilder\Where;
use Hyperf\Database\Model\Builder;
use CodeMagpie\SimpleQueryBuilder\Constants\Operator;

/**
 * @method Pagination getPagination()
 * @method OrderBy[] getOrderBys()
 * @method Where[] getWheres()
 * @method string[] getFields()
 */
trait BindHyperfQueryBuilderTrait
{
    /**
     * @param Builder|\Hyperf\Database\Query\Builder $queryBuilder
     */
    public function bindHyperfQueryBuilder($queryBuilder)
    {
        if ($pagination = $this->getPagination()) {
            $queryBuilder->forPage($pagination->getPage(), $pagination->getPerPage());
        }
        foreach ($this->getOrderBys() as $orderBy) {
            $queryBuilder->orderBy($orderBy->getField(), $orderBy->getDirection());
        }
        foreach ($this->getWheres() as $where) {
            $filed = $where->getName();
            $value = $where->getValue();
            $boolean = $where->getBoolean();
            if (is_object($value)) {
                // nested where
                $queryBuilder->where(function ($builder) use ($value) {
                    $value->bindHyperfQueryBuilder($builder);
                }, null, null, $boolean);
            } else {
                switch ($where->getOperator()) {
                    case Operator::LIKE:
                        $queryBuilder->where($filed, 'like', "%{$value}%", $boolean);

                        break;
                    case Operator::LEFT_LIKE:
                        $queryBuilder->where($filed, 'like', "{$value}%", $boolean);

                        break;
                    case Operator::RIGHT_LIKE:
                        $queryBuilder->where($filed, 'like', "%{$value}", $boolean);

                        break;
                    case Operator::IN:
                        $queryBuilder->whereIn($filed, $value, $boolean);

                        break;
                    case Operator::NOT_IN:
                        $queryBuilder->whereNotIn($filed, $value, $boolean);

                        break;
                    case Operator::BETWEEN:
                        $queryBuilder->whereBetween($filed, $value, $boolean);

                        break;
                    case Operator::OPEN_BETWEEN:
                        $queryBuilder->where(function ($builder) use ($filed, $value) {
                            $builder->where($filed, '>', $value[0])->where($filed, '<', $value[1]);
                        }, null, null, $boolean);

                        break;
                    case Operator::LEFT_OPEN_BETWEEN:
                        $queryBuilder->where(function ($builder) use ($filed, $value) {
                            $builder->where($filed, '>', $value[0])->where($filed, '<=', $value[1]);
                        }, null, null, $boolean);

                        break;
                    case Operator::RIGHT_OPEN_BETWEEN:
                        $queryBuilder->where(function ($builder) use ($filed, $value) {
                            $builder->where($filed, '>=', $value[0])->where($filed, '<', $value[1]);
                        }, null, null, $boolean);

                        break;
                    case Operator::EQUAL:
                        $queryBuilder->where($filed, '=', $value, $boolean);

                        break;
                    case Operator::NOT_EQUAL:
                        $queryBuilder->where($filed, '!=', $value, $boolean);

                        break;
                    case Operator::LESS:
                        $queryBuilder->where($filed, '<', $value, $boolean);

                        break;
                    case Operator::LESS_OR_EQUAL:
                        $queryBuilder->where($filed, '<=', $value, $boolean);

                        break;
                    case Operator::GREAT:
                        $queryBuilder->where($filed, '>', $value, $boolean);

                        break;
                    case Operator::GREAT_OR_EQUAL:
                        $queryBuilder->where($filed, '>=', $value, $boolean);

                        break;
                    case Operator::IS_NULL:
                        $queryBuilder->whereNull($filed, $boolean);

                        break;
                    case Operator::IS_NOT_NULL:
                        $queryBuilder->whereNotNull($filed, $boolean);

                        break;
                }
            }
        }
        return $queryBuilder;
    }
}
