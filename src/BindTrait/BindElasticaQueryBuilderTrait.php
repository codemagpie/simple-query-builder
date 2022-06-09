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

use CodeMagpie\SimpleQueryBuilder\Constants\Operator;
use CodeMagpie\SimpleQueryBuilder\OrderBy;
use CodeMagpie\SimpleQueryBuilder\Pagination;
use CodeMagpie\SimpleQueryBuilder\Where;
use Elastica\Query;

/**
 * @method Pagination getPagination()
 * @method OrderBy[] getOrderBys()
 * @method Where[] getWheres()
 * @method string[] getFields()
 */
trait BindElasticaQueryBuilderTrait
{
    public function bindElasticaQueryBuilder(Query $queryBuilder): Query
    {
        if ($pagination = $this->getPagination()) {
            $queryBuilder->setFrom(($pagination->getPage() - 1) * $pagination->getPerPage());
            $queryBuilder->setSize($pagination->getPerPage());
        }
        foreach ($this->getOrderBys() as $orderBy) {
            $queryBuilder->addSort([$orderBy->getField() => $orderBy->getDirection()]);
        }
        $parseWhere = function ($wheres) use (&$parseWhere) {
            $bool = new Query\BoolQuery();
            $groups = [];
            $temps = [];
            foreach ($wheres as $index => $where) {
                $filed = $where->getName();
                $value = $where->getValue();
                $query = null;
                if (is_object($where->getValue())) {
                    $query = $parseWhere($where->getValue()->getWheres());
                } else {
                    switch ($where->getOperator()) {
                        case Operator::LIKE:
                            $query = new Query\MatchPhrase($filed, $value);

                            break;
                        case Operator::LEFT_LIKE:
                            $query = new Query\MatchPhrasePrefix($filed, $value);
                            break;
                        case Operator::RIGHT_LIKE:
                            $query = new Query\Wildcard($filed, "*{$value}");

                            break;
                        case Operator::IN:
                            $query = new Query\Terms($filed, $value);

                            break;
                        case Operator::NOT_IN:
                            $query = new Query\BoolQuery();
                            $query->addMustNot(new Query\Terms($filed, $value));

                            break;
                        case Operator::BETWEEN:
                            $query = new Query\Range($filed, ['gte' => $value[0], 'lte' => $value[1]]);

                            break;
                        case Operator::OPEN_BETWEEN:
                            $query = new Query\Range($filed, ['gt' => $value[0], 'lt' => $value[1]]);

                            break;
                        case Operator::LEFT_OPEN_BETWEEN:
                            $query = new Query\Range($filed, ['gt' => $value[0], 'lte' => $value[1]]);

                            break;
                        case Operator::RIGHT_OPEN_BETWEEN:
                            $query = new Query\Range($filed, ['gte' => $value[0], 'lt' => $value[1]]);

                            break;
                        case Operator::EQUAL:
                            $query = new Query\Term([$filed => $value]);

                            break;
                        case Operator::NOT_EQUAL:
                            $query = new Query\BoolQuery();
                            $query->addMustNot(new Query\Term([$filed => $value]));

                            break;
                        case Operator::LESS:
                            $query = new Query\Range($filed, ['lt' => $value]);

                            break;
                        case Operator::LESS_OR_EQUAL:
                            $query = new Query\Range($filed, ['lte' => $value]);

                            break;
                        case Operator::GREAT:
                            $query = new Query\Range($filed, ['gt' => $value]);

                            break;
                        case Operator::GREAT_OR_EQUAL:
                            $query = new Query\Range($filed, ['gte' => $value]);

                            break;
                        case Operator::IS_NULL:
                            $query = new Query\Exists($filed);

                            break;
                        case Operator::IS_NOT_NULL:
                            $query = new Query\BoolQuery();
                            $query->addMustNot(new Query\Exists($filed));

                            break;
                    }
                }
                if (! $query) {
                    continue;
                }
                $temps[$index] = $query;
                if (($index !== 0 && $where->getBoolean() === 'or')) {
                    unset($temps[$index]);
                    $groups[] = array_values($temps);
                    $temps = [$query];
                }
                if (($index + 1) === count($wheres)) {
                    $groups[] = array_values($temps);
                }
            }
            if (count($groups) === 1) {
                foreach ($groups[0] as $item) {
                    $bool->addMust($item);
                }
            } else {
                foreach ($groups as $group) {
                    if (count($group) === 1) {
                        $bool->addShould($group[0]);
                    } else {
                        $bool2 = new Query\BoolQuery();
                        foreach ($group as $item) {
                            $bool2->addMust($item);
                        }
                        $bool->addShould($bool2);
                    }
                }
            }
            return $bool;
        };
        $queryBuilder->setQuery($parseWhere($this->getWheres()));
        return $queryBuilder;
    }
}
