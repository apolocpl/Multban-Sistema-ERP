<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class EmpresaTiposDePlanoVendido extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_tpplanovd';

    public $timestamps = false;
}
