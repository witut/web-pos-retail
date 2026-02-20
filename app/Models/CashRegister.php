<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'name',
        'status',
        'ip_address'
    ];

    public function sessions()
    {
        return $this->hasMany(CashRegisterSession::class);
    }
}
