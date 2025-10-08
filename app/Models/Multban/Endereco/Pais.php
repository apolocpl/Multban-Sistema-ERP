<?php

namespace App\Models\Multban\Endereco;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'tbdm_pais';

    public function getKeyName()
    {
        return 'pais';
    }

    public $incrementing = false;

    protected $primaryKey = 'pais';

    protected $fillable = [
        'pais',
        'langu',
        'pais_desc',
    ];
}
