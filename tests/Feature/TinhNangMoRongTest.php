<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

/** Tính năng cộng điểm: upload avatar, tìm kiếm AJAX. */
class TinhNangMoRongTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function profileData($user, array $extra = []): array
    {
        return array_merge(['name' => $user->name, 'email' => $user->email], $extra);
    }

    public function test_user_can_upload_replace_and_remove_avatar(): void
    {
        Storage::fake('public');
        $user = $this->hocVien();

        $this->actingAs($user)->put('/profile', $this->profileData($user, ['avatar' => UploadedFile::fake()->image('a.png')]))->assertSessionHasNoErrors();
        $first = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($user)->put('/profile', $this->profileData($user, ['avatar' => UploadedFile::fake()->image('b.jpg')]));
        $second = $user->fresh()->avatar;
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->actingAs($user)->put('/profile', $this->profileData($user, ['remove_avatar' => '1']));
        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_avatar_rejects_non_image_and_large_file(): void
    {
        Storage::fake('public');
        $user = $this->hocVien();

        $this->actingAs($user)->put('/profile', $this->profileData($user, ['avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('avatar');
        $this->actingAs($user)->put('/profile', $this->profileData($user, ['avatar' => UploadedFile::fake()->image('big.jpg')->size(3000)]))
            ->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_ajax_search_returns_only_the_list_fragment(): void
    {
        $inst = $this->giangVien();
        $this->taoKhoaHoc($inst)->update(['title' => 'Laravel nâng cao']);
        $this->taoKhoaHoc($inst)->update(['title' => 'Python']);

        $res = $this->get('/courses?q=Laravel', ['X-Requested-With' => 'XMLHttpRequest']);
        $res->assertOk()->assertSee('Laravel nâng cao')->assertDontSee('Python')->assertDontSee('<html', false);

        $this->get('/courses?q=Laravel')->assertSee('<html', false);
    }
}
