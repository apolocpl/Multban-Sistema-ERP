<?php

namespace App\Models\Multban\ProgramaPts;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Model;

class ProgramaPtsStatus extends Model
{
    use DbSysClientTrait;

    protected $table = "tbdm_prgpts_sts";

    public $timestamps = false;
}
