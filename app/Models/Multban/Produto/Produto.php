<?php

namespace App\Models\Multban\Produto;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'emp_id',
        'produto_id',
        'produto_sts',
        'produto_tipo',
        'partcp_pvlaor',
        'partcp_empid',
        'partcp_seller',
        'partcp_pgsplit',
        'partcp_pgtransf',
        'partcp_cdgbc',
        'partcp_agbc',
        'partcp_ccbc',
        'partcp_pix',
        'produto_ncm',
        'produto_cdgb',
        'produto_peso',
        'produto_ctrl',
        'produto_dc',
        'produto_dm',
        'produto_dl',
        'produto_dt',
        'produto_vlr',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch',
    ];
    use DbSysClientTrait;

    protected $table = "tbdm_produtos_geral";

    public $timestamps = false;

    public function getKeyName()
    {
        return "produto_id";
    }

    protected $primaryKey = 'produto_id';

    public function status()
    {
        return $this->belongsTo(ProdutoStatus::class, 'produto_sts', 'produto_sts');
    }

    public function tipo()
    {
        return $this->belongsTo(ProdutoTipo::class, 'produto_tipo', 'produto_tipo');
    }
}
