<?php

namespace App\Models\Multban\TbTr;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbtrfTitulos extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbtr_f_titulos';

    public $timestamps = false;

    protected $fillable = [
        'emp_id',
        'id_fatura',
        'cliente_id',
        'card_uuid',
        'integ_bc',
        'fatura_sts',
        'data_fech',
        'data_venc',
        'data_pgto',
        'vlr_tot',
        'vlr_pgto',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch',
    ];
}
