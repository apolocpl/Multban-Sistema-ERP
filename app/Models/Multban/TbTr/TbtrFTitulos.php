<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;


class TbtrfTitulos extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_f_titulos';
    public $timestamps = false;
    protected $fillable = [
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
