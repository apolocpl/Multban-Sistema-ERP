<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmUserStatus extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_userstatus';

    public $timestamps = false;
}
