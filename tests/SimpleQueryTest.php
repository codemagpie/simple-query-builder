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
}
