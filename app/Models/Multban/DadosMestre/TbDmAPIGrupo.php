<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmAPIGrupo extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_api_grupo';

    public $timestamps = false;
}
