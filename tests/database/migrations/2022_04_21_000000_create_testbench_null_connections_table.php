<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateTestbenchNullConnectionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('null_connections', function (Blueprint $table): void {
            $table->increments('id');
        });
    }

    public function down(): void
    {
        Schema::drop('null_connections');
    }
}
