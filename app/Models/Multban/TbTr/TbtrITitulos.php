<?php

namespace App\Models\Multban\TbTr;

use Illuminate\Database\Eloquent\Model;
use App\Models\Multban\Traits\DbSysClientTrait;

class TbtrITitulos extends Model
{
    use DbSysClientTrait;
    protected $table = 'tbtr_i_titulos';
    public $timestamps = false;
    protected $fillable = [
        'emp_id',
        'user_id',
        'titulo',
        'nsu_titulo',
        'nsu_autoriz',
        'item',
        'produto_tipo',
        'produto_id',
        'qtd_item',
        'vlr_unit_item',
        'vlr_brt_item',
        'perc_toti',
        'qtd_pts_utlz_item',
        'vlr_base_item',
        'vlr_dec_item',
        'vlr_dec_mn',
        'vlr_bpar_split_item',
        'vlr_jpar_item',
        'vlr_bpar_cj_item',
        'vlr_atrm_item',
        'vlr_atrj_item',
        'vlr_acr_mn',
        'ant_desc',
        'pgt_vlr',
        'pgt_desc',
        'pgt_mtjr',
        'vlr_rec',
        'pts_disp',
    ];
}
