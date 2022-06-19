<?php

namespace App\Queries;

class OrderQuery
{
    /**
     * Build a query to get orders (sales).
     *
     * @return string
     */
    public static function buildGetTotalSales()
    {
        $query = <<<QUERY
        SELECT c.id, SUM(i.price) as total
        FROM courses c
        INNER JOIN order_items i ON i.course_id = c.id
        INNER JOIN order_histories h ON h.order_id = i.order_id
        WHERE h.status = ? AND c.user_id = ?
        GROUP BY c.id
        QUERY;

        return $query;
    }
}