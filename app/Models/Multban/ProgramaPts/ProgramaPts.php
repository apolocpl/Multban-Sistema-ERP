<?php

namespace App\Models\Multban\ProgramaPts;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ProgramaPts extends Model
{
    use DbSysClientTrait;

    protected $table = 'tbdm_programa_pts';

    protected $primaryKey = 'prgpts_id';

    public $timestamps = false;
}
