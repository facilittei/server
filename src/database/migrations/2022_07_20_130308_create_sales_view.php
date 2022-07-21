<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    /**
     * Create the sales view.
     */
    private function createView(): string
    {
        return <<<QUERY
        CREATE VIEW sales_view AS 
        SELECT 
            i.course_id,
            o.id order_id, 
            o.reference,
            h.status,
            o.total, 
            f.percentage,
            f.transaction,
            (o.total - (o.total * (f.percentage / 100)) - f.transaction) as net
        FROM orders o
        INNER JOIN order_items i ON i.order_id = o.id
        INNER JOIN order_histories h ON h.order_id = o.id
        INNER JOIN fees f ON f.order_id = o.id;
        QUERY;
    }

    /**
     * Drop the sales view.
     */
    private function dropView(): string
    {
        return 'DROP VIEW IF EXISTS sales_view';
    }
};
