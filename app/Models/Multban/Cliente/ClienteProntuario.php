<?php

namespace App\Models\Multban\Cliente;

use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Traits\DbSysClientTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ClienteProntuario extends Model
{
    use DbSysClientTrait;

    protected $table = "tbdm_clientes_prt";

    protected $primaryKey = "protocolo";

    public $timestamps = false;

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function tipo()
    {
        return $this->belongsTo(ClienteProntuarioTipo::class, 'protocolo_tp', 'protocolo_tp');
    }

    protected $fillable = [
        'protocolo',
        'protocolo_tp',
        'protocolo_td',
        'cliente_id',
        'emp_id',
        'user_id',
        'texto_prt',
        'texto_anm',
        'texto_rec',
        'dthr_cr',
        'dthr_ch'
    ];
}
