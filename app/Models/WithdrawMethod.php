<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawMethod extends Model
{
    use HasFactory;

    protected $table = 'withdraw_methods';
    public $timestamps = false;

    protected $fillable = [
        'method',
        'minimum',
    ];
}
