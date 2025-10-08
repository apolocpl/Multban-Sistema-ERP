<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class CardStatus extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_card_sts';

    protected $primaryKey = 'card_sts';

    public $timestamps = false;
}
