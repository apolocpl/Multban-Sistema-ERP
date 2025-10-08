<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmCanalCm extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_canal_cm';

    public $timestamps = false;
}
