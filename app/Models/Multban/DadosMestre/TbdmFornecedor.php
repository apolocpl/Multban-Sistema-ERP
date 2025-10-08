<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbdmFornecedor extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_fornec';

    public $timestamps = false;
}
