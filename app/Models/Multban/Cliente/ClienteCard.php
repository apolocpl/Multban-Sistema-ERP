<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ClienteCard extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_clientes_card';

    public $timestamps = false;
}
