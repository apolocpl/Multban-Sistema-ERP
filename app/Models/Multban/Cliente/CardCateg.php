<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class CardCateg extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_card_categ';

    public $timestamps = false;
}
