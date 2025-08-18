<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmAgendamentoTipo extends Model
{

    use DbSysClientTrait;

    protected $table = "tbdm_agendamento_tp";

    public $timestamps = false;

    protected $primaryKey = 'agendamento_tipo';

    protected $fillable = [
        'agendamento_sts',
        'langu',
        'agendamento_sts_desc',
    ];

}
