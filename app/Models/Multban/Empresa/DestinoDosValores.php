<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class DestinoDosValores extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_destvlr';

    public $timestamps = false;
}
