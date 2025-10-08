<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class EmpresaTipoDeBoletagem extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_tpbolet';

    public $timestamps = false;
}
