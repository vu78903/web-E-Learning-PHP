<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Nâng cấp bài kiểm tra: xáo trộn câu hỏi, giới hạn số lần làm, câu nhiều đáp án đúng. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('shuffle_questions')->default(false)->after('pass_score');
            $table->unsignedSmallInteger('max_attempts')->nullable()->after('shuffle_questions'); // null = không giới hạn
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('type', 10)->default('single')->after('question_text'); // single | multiple
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Thứ tự câu hỏi/phương án đã xáo cho lượt làm: {"questions": [...], "answers": {qid: [...]}}
            $table->json('question_order')->nullable()->after('total_questions');
        });

        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            // Danh sách phương án đã chọn (câu nhiều đáp án); answer_id giữ cho câu một đáp án.
            $table->json('answer_ids')->nullable()->after('answer_id');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', fn (Blueprint $table) => $table->dropColumn('answer_ids'));
        Schema::table('quiz_attempts', fn (Blueprint $table) => $table->dropColumn('question_order'));
        Schema::table('questions', fn (Blueprint $table) => $table->dropColumn('type'));
        Schema::table('quizzes', fn (Blueprint $table) => $table->dropColumn(['shuffle_questions', 'max_attempts']));
    }
};
