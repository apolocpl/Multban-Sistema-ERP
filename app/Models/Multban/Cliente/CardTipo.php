<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class CardTipo extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_card_tp';

    protected $primaryKey = 'card_tp';

    public $timestamps = false;
}
