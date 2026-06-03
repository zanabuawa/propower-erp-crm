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
        // 1. Bank of Tests (Templates)
        Schema::create('hr_test_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('role_target')->nullable(); // e.g. 'segurista', 'supervisor'
            $table->integer('total_points')->default(100);
            $table->integer('passing_score')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Questions for Templates
        Schema::create('hr_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_test_template_id')->constrained('hr_test_templates')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('type', ['multiple_choice', 'open_ended'])->default('multiple_choice');
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Options for Multiple Choice Questions
        Schema::create('hr_test_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_test_question_id')->constrained('hr_test_questions')->onDelete('cascade');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 4. Evaluation Process for a Prospect
        Schema::create('hr_evaluation_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_prospect_id')->constrained('hr_prospects')->onDelete('cascade');
            $table->integer('current_stage_index')->default(1);
            $table->integer('total_stages')->default(4);
            $table->enum('status', ['active', 'completed', 'canceled'])->default('active');
            $table->timestamps();
        });

        // 5. Stages within a Process
        Schema::create('hr_evaluation_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_evaluation_process_id')->constrained('hr_evaluation_processes')->onDelete('cascade');
            $table->string('name');
            $table->integer('order')->default(1);
            $table->string('guide_path')->nullable(); // For uploading PDF/Guides
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });

        // 6. Test Instances (Specific test taken by a prospect in a stage)
        Schema::create('hr_prospect_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_evaluation_stage_id')->constrained('hr_evaluation_stages')->onDelete('cascade');
            $table->foreignId('hr_test_template_id')->constrained('hr_test_templates');
            $table->decimal('score', 8, 2)->default(0);
            $table->enum('status', ['pending', 'submitted', 'graded'])->default('pending');
            $table->foreignId('graded_by_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 7. Answers submitted by the prospect/candidate
        Schema::create('hr_prospect_test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_prospect_test_id')->constrained('hr_prospect_tests')->onDelete('cascade');
            $table->foreignId('hr_test_question_id')->constrained('hr_test_questions');
            $table->text('answer_text')->nullable(); // For open-ended
            $table->foreignId('hr_test_option_id')->nullable()->constrained('hr_test_options'); // For MCQ
            $table->boolean('is_correct')->nullable(); // Auto-filled for MCQ, manual for open-ended
            $table->integer('points_earned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_prospect_test_answers');
        Schema::dropIfExists('hr_prospect_tests');
        Schema::dropIfExists('hr_evaluation_stages');
        Schema::dropIfExists('hr_evaluation_processes');
        Schema::dropIfExists('hr_test_options');
        Schema::dropIfExists('hr_test_questions');
        Schema::dropIfExists('hr_test_templates');
    }
};
