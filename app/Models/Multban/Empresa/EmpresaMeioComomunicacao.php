<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class EmpresaMeioComomunicacao extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_meiocom';

    public $timestamps = false;
}
