<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DanhMucController extends Controller
{
    public function danhSach(): View
    {
        return view('admin.categories.index', [
            'categories' => DanhMuc::withCount('courses')->orderBy('name')->get(),
        ]);
    }

    public function taoMoi(): View
    {
        return view('admin.categories.form', ['category' => new DanhMuc]);
    }

    public function luu(Request $request): RedirectResponse
    {
        DanhMuc::create($this->duLieuHopLe($request));

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm danh mục.');
    }

    public function chinhSua(DanhMuc $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function capNhat(Request $request, DanhMuc $category): RedirectResponse
    {
        $category->update($this->duLieuHopLe($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function xoa(DanhMuc $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with('error', 'Danh mục còn khóa học nên không thể xóa.');
        }
        $category->delete();

        return back()->with('success', 'Đã xóa danh mục.');
    }

    private function duLieuHopLe(Request $request, ?DanhMuc $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
