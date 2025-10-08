<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmLangu extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_langu';

    public $timestamps = false;
}
