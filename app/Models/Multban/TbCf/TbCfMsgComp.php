<?php

namespace App\Models\Multban\TbCf;

use App\Models\Multban\DadosMestre\TbDmCanalCm;
use App\Models\Multban\DadosMestre\TbDmMsgCateg;
use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbCfMsgComp extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbcf_msg_comp';

    public $timestamps = false;

    public function canal()
    {
        return $this->belongsTo(TbDmCanalCm::class, 'canal_id', 'canal_id');
    }

    public function categoria()
    {
        return $this->belongsTo(TbDmMsgCateg::class, 'msg_categ', 'msg_categ');
    }
}
