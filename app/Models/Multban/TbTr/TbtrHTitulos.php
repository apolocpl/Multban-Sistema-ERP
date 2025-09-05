<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;

class TbtrHTitulos extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbtr_h_titulos';
    public $timestamps = false;
    protected $primaryKey = 'titulo';
    protected $keyType = 'int';
    protected $fillable = [
        'emp_id', 'user_id', 'titulo', 'nid_titulo', 'qtd_parc', 'primeira_para', 'cnd_pag', 'cliente_id', 'meio_pag', 'card_uuid', 'data_mov', 'check_reemb', 'vlr_brt', 'tax_adm', 'tax_rebate', 'tax_royalties', 'tax_comissao', 'qtd_pts_utlz', 'perc_pts_utlz', 'vlr_btot', 'perc_desc', 'vlr_dec', 'vlr_btot_split', 'perc_juros', 'vlr_juros', 'vlr_btot_cj', 'vlr_atr_m', 'vlr_atr_j', 'vlr_acr_mn'
    ];
}
