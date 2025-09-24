<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;

class TbtrPTitulosAb extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_p_titulos_ab';
    public $timestamps = false;
    protected $fillable = [
        'emp_id',
        'user_id',
        'titulo',
        'nsu_titulo',
        'nsu_autoriz',
        'qtd_parc',
        'primeira_para',
        'cnd_pag',
        'cliente_id',
        'meio_pag_v',
        'data_mov',
        'parcela',
        'nid_parcela',
        'data_venc',
        'parcela_sts',
        'destvlr',
        'card_uuid',
        'id_fatura',
        'integ_bc',
        'data_pgto',
        'meio_pag_t',
        'nid_parcela_org',
        'parcela_obs',
        'parcela_ins_pg',
        'qtd_pts_utlz',
        'tax_bacen',
        'vlr_dec',
        'vlr_dec_mn',
        'vlr_bpar_split',
        'vlr_jurosp',
        'vlr_bpar_cj',
        'vlr_atr_m',
        'vlr_atr_j',
        'isent_mj',
        'negociacao',
        'vlr_acr_mn',
        'negociacao_obs',
        'negociacao_file',
        'follow_dt',
        'perct_ant',
        'ant_desc',
        'pgt_vlr',
        'pgt_desc',
        'pgt_mtjr',
        'vlr_rec',
        'pts_disp_item',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch',
    ];
}
