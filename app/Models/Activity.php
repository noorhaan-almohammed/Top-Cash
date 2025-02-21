<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'users_activities';
    public $timestamps = false;
    protected $fillable = ['user_id', 'notify_id', 'value', 'read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
