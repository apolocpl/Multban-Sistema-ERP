<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaParam extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'tbdm_empresa_param';

    public $timestamps = false;

    public function getKeyName()
    {
        return 'emp_id';
    }

    protected $primaryKey = 'emp_id';

    public $incrementing = false;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id');
    }
}
