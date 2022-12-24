<?php

namespace App\Queries;

class OrderQuery
{
    /**
     * Build a query to get orders (sales).
     *
     * @return string
     */
    public static function buildGetTotalSales(): string
    {
        $query = <<<'QUERY'
        SELECT c.id, SUM(s.net) as total
        FROM sales_view s
        INNER JOIN courses c ON c.id = s.course_id
        WHERE s.status = ? AND c.user_id = ?
        GROUP BY c.id
        QUERY;

        return $query;
    }
}
