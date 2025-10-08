<?php

namespace App\Models\Multban\TbCf;

use App\Models\Multban\DadosMestre\TbdmFornecedor;
use App\Models\Multban\Empresa\Empresa;
use Illuminate\Database\Eloquent\Model;

class ConexoesBcEmp extends Model
{
    protected $table = 'tbsy_conexoes_bc_emp';

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(TbdmFornecedor::class, 'bc_fornec', 'fornec');
    }
}
