<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmMsgCateg extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_msg_categ';

    public $timestamps = false;
}
