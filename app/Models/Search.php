<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Search extends Model
{
    protected $table = 'records';

    protected $fillable = ['content'];

    public function usages(): HasMany
    {
        return $this->hasMany(SearchUsage::class);
    }
}
