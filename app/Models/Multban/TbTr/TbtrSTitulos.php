<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;

class TbtrSTitulos extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_s_titulos';
    public $timestamps = false;
    protected $fillable = [
        'emp_id',
        'user_id',
        'titulo',
        'nsu_titulo',
        'nsu_autoriz',
        'parcela',
        'produto_id',
        'lanc_tp',
        'recebedor',
        'tax_adm',
        'vlr_plan',
        'perc_real',
        'vlr_real',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch',
    ];
}
