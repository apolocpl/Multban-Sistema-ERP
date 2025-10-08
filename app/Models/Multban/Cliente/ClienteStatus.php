<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ClienteStatus extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_cliente_sts';

    public $timestamps = false;
}
