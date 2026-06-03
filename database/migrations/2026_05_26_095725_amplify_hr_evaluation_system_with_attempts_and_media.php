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
        // 1. Amplify Evaluation Stages to support multiple media (Video links)
        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->json('video_links')->nullable()->after('guide_path'); // Store array of links
        });

        // 2. Amplify Prospect Test instances to support attempt limits and history
        Schema::table('hr_prospect_tests', function (Blueprint $table) {
            $table->integer('max_attempts')->default(1)->after('hr_test_template_id');
            $table->integer('attempts_count')->default(0)->after('max_attempts');
        });

        // 3. Create a table to store each specific ATTEMPT
        Schema::create('hr_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_prospect_test_id')->constrained('hr_prospect_tests')->onDelete('cascade');
            $table->integer('attempt_number');
            $table->decimal('score', 8, 2)->default(0);
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 4. Move answers to point to ATTEMPTS instead of the general Test record
        Schema::table('hr_prospect_test_answers', function (Blueprint $table) {
            $table->dropForeign(['hr_prospect_test_id']);
            $table->renameColumn('hr_prospect_test_id', 'hr_test_attempt_id');
            $table->foreign('hr_test_attempt_id')->references('id')->on('hr_test_attempts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_prospect_test_answers', function (Blueprint $table) {
            $table->dropForeign(['hr_test_attempt_id']);
            $table->renameColumn('hr_test_attempt_id', 'hr_prospect_test_id');
            $table->foreign('hr_prospect_test_id')->references('id')->on('hr_prospect_tests')->onDelete('cascade');
        });

        Schema::dropIfExists('hr_test_attempts');

        Schema::table('hr_prospect_tests', function (Blueprint $table) {
            $table->dropColumn(['max_attempts', 'attempts_count']);
        });

        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->dropColumn('video_links');
        });
    }
};
