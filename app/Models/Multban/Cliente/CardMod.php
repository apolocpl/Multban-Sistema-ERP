<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class CardMod extends Model
{
    use DbSysClientTrait;
    protected $table = "tbdm_card_mod";
    protected $primaryKey = 'card_mod';
    public $timestamps = false;
}
