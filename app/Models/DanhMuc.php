<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMuc extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'description'];

    public function courses(): HasMany
    {
        return $this->hasMany(KhoaHoc::class, 'category_id');
    }
}
