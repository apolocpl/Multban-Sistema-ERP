<?php

namespace App\Models\Multban\TbCf;

use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Traits\DbSysClientTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TbCfWorkFlow extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbcf_config_wf';

    public $timestamps = false;

    public function canal()
    {
        // return $this->belongsTo(TbDmCanalCm::class, 'canal_id', 'canal_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
