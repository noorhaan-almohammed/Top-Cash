<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigSite extends Model
{
    use HasFactory;
    protected $table = "site_config";

    protected $fillable = ['config_name', 'config_value'];
}
