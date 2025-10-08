<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class EmpresaTipoDeAdquirentes extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_adquirentes';

    public $timestamps = false;
}
