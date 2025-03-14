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
        'icon_url'
    ];

    public $appends = [
        'required_coins'
    ];
    public function getRequiredCoinsAttribute(){
        $coins_rate = SiteConfig::where('config_name', 'coins_rate')->value('config_value');
        return $this->minimum*$coins_rate;
    }
}
