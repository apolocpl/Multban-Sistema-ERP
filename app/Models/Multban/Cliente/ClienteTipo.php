<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ClienteTipo extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_cliente_tp';

    public $timestamps = false;
}
