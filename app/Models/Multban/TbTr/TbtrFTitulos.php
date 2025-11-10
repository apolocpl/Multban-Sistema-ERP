<?php

namespace App\Models\Multban\TbTr;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbtrfTitulos extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbtr_f_titulos';

    // A chave primária da tabela é id_fatura (UUID/string)
    protected $primaryKey = 'id_fatura';

    // id_fatura não é auto-increment (é UUID/strings)
    public $incrementing = false;
    protected $keyType = 'string';

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
