<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id'); // Foreign key column
            $table->string('branch_name');
            $table->string('mail_id');
            $table->string('mobile_number');
            $table->string('phone_number');
            $table->string('branch_address');
            $table->string('tin_number');
            $table->string('gst_number');


            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_branches');
    }
};
