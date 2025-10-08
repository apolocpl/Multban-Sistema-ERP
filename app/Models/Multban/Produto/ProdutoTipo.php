<?php

namespace App\Models\Multban\Produto;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ProdutoTipo extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_produto_tp';

    public $timestamps = false;
}
