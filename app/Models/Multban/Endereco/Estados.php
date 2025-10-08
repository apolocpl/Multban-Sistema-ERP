<?php

namespace App\Models\Multban\Endereco;

use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estados extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'tbdm_estados';

    public function getKeyName()
    {
        return 'estado';
    }

    public $incrementing = false;

    protected $primaryKey = 'estado';

    protected $fillable = [
        'estado_pais',
        'estado',
        'langu',
        'estado_desc',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'estado_pais', 'pais');
    }
}
