<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToPenjualansTable extends Migration
{
    public function up()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_at']);
        });
    }
}
