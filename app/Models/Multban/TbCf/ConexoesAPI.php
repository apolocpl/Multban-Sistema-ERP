<?php

namespace App\Models\Multban\TbCf;

use App\Models\Multban\DadosMestre\TbDmAPIGrupo;
use App\Models\Multban\DadosMestre\TbDmAPISubGrupo;
use App\Models\Multban\DadosMestre\TbdmFornecedor;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ConexoesAPI extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbcf_conexoes_api_emp';

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(TbdmFornecedor::class, 'bc_fornec', 'fornec');
    }

    public function grupo()
    {
        return $this->belongsTo(TbDmAPIGrupo::class, 'api_grupo', 'api_grupo');
    }

    public function subgrupo()
    {
        return $this->belongsTo(TbDmAPISubGrupo::class, 'api_subgrp', 'api_subgrp');
    }
}
