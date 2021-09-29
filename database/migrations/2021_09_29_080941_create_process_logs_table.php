<?php

use App\Models\Process;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_logs', function (Blueprint $table) {
            $table->id();
            $table->text('headers')->nullable();
            $table->text('body')->nullable();
            $table->integer('total_limit')->default(0);
            $table->integer('total_left')->default(0);
            $table->datetime('retry_at')->nullable();
            $table->foreignIdFor(Process::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_logs');
    }
}
