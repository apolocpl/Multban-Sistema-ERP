<?php

namespace App\Models\Multban\Venda;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegrasParc extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'tbdm_regra_parc';
}
