<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmUserFunc extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_userfunc';

    public $timestamps = false;
}
