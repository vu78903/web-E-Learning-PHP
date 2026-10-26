<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_attachments', function (Blueprint $table) {
            // SHA-256 nội dung file, dùng để phát hiện tài liệu trùng (bản ghi cũ được tính bù khi cần).
            $table->char('hash', 64)->nullable()->after('mime_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('lesson_attachments', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });
    }
};
