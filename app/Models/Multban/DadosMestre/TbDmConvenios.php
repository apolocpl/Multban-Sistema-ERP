<?php

namespace App\Models\Multban\DadosMestre;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class TbDmConvenios extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_convenios';

    protected $primaryKey = 'convenio_id';

    protected $fillable = [
        'convenio_id',
        'convenio_desc',
    ];

    public $timestamps = false;
}
