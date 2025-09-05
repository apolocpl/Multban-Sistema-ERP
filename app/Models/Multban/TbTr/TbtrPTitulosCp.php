<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;

class TbtrPTitulosCp extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_p_titulos_cp';
    public $timestamps = false;
    protected $fillable = [
        'emp_id', 'user_id', 'titulo', 'nid_titulo', 'qtd_parc', 'primeira_para', 'cnd_pag', 'cliente_id', 'meio_pag_v', 'card_uuid', 'data_mov', 'parcela', 'nid_parcela', 'id_fatura', 'integ_bc', 'data_venc', 'data_pgto', 'meio_pag_t', 'parcela_sts', 'destvlr', 'nid_parcela_org', 'parcela_obs', 'parcela_ins_pg', 'qtd_pts_utlz', 'tax_bacen', 'vlr_dec', 'vlr_dec_mn', 'vlr_bpar_split', 'vlr_jurosp', 'vlr_bpar_cj', 'vlr_atr_m', 'vlr_atr_j', 'isent_mj', 'negociacao', 'vlr_acr_mn', 'negociacao_obs', 'follow_dt', 'perct_ant', 'ant_desc', 'pgt_vlr', 'pgt_desc', 'pgt_mtjr', 'vlr_rec', 'pts_disp_item'
    ];
}
