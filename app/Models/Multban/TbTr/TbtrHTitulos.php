<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;


class TbtrHTitulos extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_h_titulos';
    protected $primaryKey = 'titulo';
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'emp_id',
        'user_id',
        'titulo',
        'nsu_titulo',
        'qtd_parc',
        'primeira_para',
        'cnd_pag',
        'cliente_id',
        'meio_pag_v',
        'card_uuid',
        'data_mov',
        'nsu_autoriz',
        'check_reemb',
        'lib_ant',
        'vlr_brt',
        'tax_adm',
        'tax_rebate',
        'tax_royalties',
        'tax_comissao',
        'qtd_pts_utlz',
        'perc_pts_utlz',
        'vlr_btot',
        'perc_desc',
        'vlr_dec',
        'vlr_dec_mn',
        'vlr_btot_split',
        'perc_juros',
        'vlr_juros',
        'vlr_btot_cj',
        'vlr_atr_m',
        'vlr_atr_j',
        'vlr_acr_mn',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch',
    ];
}
