<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
