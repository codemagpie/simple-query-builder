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
namespace CodeMagpie\SimpleQueryBuilderTests\Stubs;

use CodeMagpie\SimpleQueryBuilder\AbstractSimpleQuery;


class UserQuery extends AbstractSimpleQuery
{
    protected array $fields = ['id', 'name', 'age'];
}
