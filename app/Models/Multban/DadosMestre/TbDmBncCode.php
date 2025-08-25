<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmBncCode extends Model
{
    use DbSysClientTrait;

    protected $table = "tbdm_bnccode";

    protected $primaryKey = 'cdgbc';

    public $timestamps = false;
}
