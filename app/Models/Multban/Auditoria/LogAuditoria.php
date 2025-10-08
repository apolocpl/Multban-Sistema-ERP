<?php

namespace App\Models\Multban\Auditoria;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAuditoria extends Model
{
    use HasFactory;

    protected $table = 'tbsy_log_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'audseq',
        'auddat',
        'audusu',
        'audtar',
        'audarq',
        'audlan',
        'audant',
        'auddep',
        'audnip',
    ];
}
