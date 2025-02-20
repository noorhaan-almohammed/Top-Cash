<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Withdrawal extends Model
{
    use HasFactory;

    protected $table = 'withdrawals';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'coins',
        'amount',
        'method_id',
        'method_name',
        'payment_info',
        'ip_address',
        'time',
    ];
    public function scopeStatus($query, $status = null)
    {
        return $query->when(!is_null($status), fn($q) => $q->where('status', $status));
    }
}
