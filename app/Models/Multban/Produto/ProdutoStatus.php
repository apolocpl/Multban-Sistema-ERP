<?php

namespace App\Models\Multban\Produto;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ProdutoStatus extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_produto_sts';

    public $timestamps = false;
}
