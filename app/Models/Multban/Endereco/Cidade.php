<?php

namespace App\Models\Multban\Endereco;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'tbdm_cidade';

    public function estado()
    {
        return $this->belongsTo(Estados::class, 'cidade_est');
    }
}
