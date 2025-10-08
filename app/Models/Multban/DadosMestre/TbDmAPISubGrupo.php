<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmAPISubGrupo extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_api_subgrp';

    public $timestamps = false;
}
