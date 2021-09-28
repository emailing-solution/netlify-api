<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('site_id');
            $table->string('identity_id');
            $table->json('emails');
            $table->string('status')->default('start');
            $table->string('pid');
            $table->integer('split_by')->default(30);
            $table->integer('delay_by')->default(0);
            $table->integer('total_sent')->default(0);
            $table->integer('total_emails')->default(0);
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('processes');
    }
}
