<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmCardCateg extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_card_categ';

    public $timestamps = false;
}
