<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrustpilotSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trustpilot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('trustpilot_settings')->insert([
            [
                'key' => 'days_before_expiry',
                'value' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'review_url',
                'value' => 'https://www.trustpilot.com/evaluate/your-business',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enabled',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trustpilot_settings');
    }
}
