<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ClienteProntuarioTipo extends Model
{
    use DbSysClientTrait;

    protected $table = "tbdm_prt_tp";

    public $timestamps = false;
}
