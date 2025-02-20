<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompletedOffer extends Model
{
    use HasFactory;
    protected $table = "completed_offers";

    public function scopeStatus($query, $status = null)
    {
        return $query->when(!is_null($status), fn($q) => $q->where('status', $status));
    }
}
