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

class Pagination
{
    protected int $page;

    protected int $perPage;

    public function __construct(int $page, int $perPage)
    {
        $this->page = $page;
        $this->perPage = $perPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
