<?php

namespace App\Models\Multban\TbCf;

use App\Models\Multban\Empresa\Empresa;
use Illuminate\Database\Eloquent\Model;

class ConfigWhiteLabel extends Model
{
    protected $table = 'tbcf_config_wl';

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }
}
