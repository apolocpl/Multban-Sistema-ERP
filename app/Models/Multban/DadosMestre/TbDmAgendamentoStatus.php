<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmAgendamentoStatus extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_agendamento_sts';

    public $timestamps = false;
}
