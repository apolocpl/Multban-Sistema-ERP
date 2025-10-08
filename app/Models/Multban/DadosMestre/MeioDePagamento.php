<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class MeioDePagamento extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_meio_pag';

    public $timestamps = false;
}
