<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ClienteScore extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_clientes_score';

    public $timestamps = false;
}
