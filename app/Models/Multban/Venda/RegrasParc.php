<?php

namespace App\Models\Multban\Venda;

use App\Models\Multban\TbCf\ConexoesBcEmp;
use App\Models\Multban\TbCf\ConfigWhiteLabel;
use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegrasParc extends Model
{
    use HasFactory, DbSysClientTrait;

    protected $table = 'tbdm_regra_parc';


}
