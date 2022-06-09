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
namespace CodeMagpie\SimpleQueryBuilderTests;

use CodeMagpie\SimpleQueryBuilder\Constants\Direction;
use CodeMagpie\SimpleQueryBuilder\OrderBy;
use CodeMagpie\SimpleQueryBuilder\Pagination;
use CodeMagpie\SimpleQueryBuilderTests\Stubs\UserQuery;
use Elastica\Query;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SimpleQueryTest extends TestCase
{
    public function testQuery(): void
    {
        $query = UserQuery::build()
            ->whereIn('id', [1, 2])
            ->whereEqual('name', 'test')
            ->addNestedOrWhere(function ($query) {
                $query->whereGreat('age', 10)->orWhereLess('age', 8);
            })
            ->orderByDesc('id')
            ->forPage(1, 10);
        self::assertEquals([1, 2], $query->getWheres()[0]->getValue());
        self::assertEquals('test', $query->getWheres()[1]->getValue());
        self::assertEquals('or', $query->getWheres()[2]->getBoolean());
        self::assertEquals('10', $query->getWheres()[2]->getValue()->getWheres()[0]->getValue());
        self::assertEquals('great', $query->getWheres()[2]->getValue()->getWheres()[0]->getOperator());
        self::assertEquals([new OrderBy('id', Direction::DESC)], $query->getOrderBys());
        self::assertEquals(new Pagination(1, 10), $query->getPagination());
    }

    public function testIllegalField(): void
    {
        $this->expectErrorMessage('illegal field address');
        UserQuery::build()->where('address', '=', '1111');
    }

    public function testIllegalOperator(): void
    {
        $this->expectErrorMessage('illegal where operator sss');
        UserQuery::build()->where('name', 'sss', '1111');
    }

    public function testIllegalBoolean(): void
    {
        $this->expectErrorMessage('illegal where boolean aa');
        UserQuery::build()->whereEqual('id', 1)->where('name', '=', '1111', 'aa');
    }

    public function testBindElasticaQueryBuilder(): void
    {
        $query = UserQuery::build()
            ->whereEqual('id', 1)
            ->whereBetween('id', [1, 2])
            ->whereIn('id', [3, 2])
            ->whereLike('name', 'hh')
            ->whereLeftLike('name', 'xx')
            ->orWhereEqual('age', 3)
            ->addNestedOrWhere(function (UserQuery $query) {
                $query->whereLess('age', 6)->orWhereIsNotNull('age')->addNestedWhere(function (UserQuery $query) {
                    $query->whereEqual('name', 8)->orWhereEqual('age', 9);
                });
            })
            ->forPage(1, 3)
            ->orderByDesc('id')
            ->orderBy('age')
            ->bindElasticaQueryBuilder(new Query());

        $compareQuery = new Query();
        $bool = new Query\BoolQuery();
        $should1 = new Query\BoolQuery();
        $should1->addMust(new Query\Term(['id' => 1]));
        $should1->addMust(new Query\Range('id', ['gte' => 1, 'lte' => 2]));
        $should1->addMust(new Query\Terms('id', [3, 2]));
        $should1->addMust(new Query\MatchPhrase('name', 'hh'));
        $should1->addMust(new Query\MatchPhrasePrefix('name', 'xx'));
        $bool->addShould($should1);

        $bool->addShould(new Query\Term(['age' => 3]));

        $should3 = new Query\BoolQuery();
        $should3->addShould(new Query\Range('age', ['lt' => 6]));
        $should31 = new Query\BoolQuery();
        $should31->addMust((new Query\BoolQuery())->addMustNot(new Query\Exists('age')));
        $should32 = new Query\BoolQuery();
        $should32->addShould(new Query\Term(['name' => 8]));
        $should32->addShould(new Query\Term(['age' => 9]));
        $should31->addMust($should32);
        $should3->addShould($should31);
        $bool->addShould($should3);

        $compareQuery->setQuery($bool)->setFrom(0)->setSize(3)->addSort(['id' => 'desc'])->addSort(['age' => 'asc']);

        self::assertEquals($compareQuery, $query);
    }
}
