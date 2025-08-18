<?php

namespace App\Models\Multban\Agendamento;

use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\Traits\DbSysClientTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    use DbSysClientTrait;
    protected $table = "tbtr_agendamento";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'agendamento_tipo',
        'cliente_id',
        'user_id',
        'prontuario_id',
        'convenio',
        'nro_carteirinha',
        'title',
        'description',
        'date',
        'start',
        'end',
        'observacao',
        'backgroundColor',
        'borderColor',
        'textColor',
        'status',
        'criador',
        'dthr_cr',
        'modificador',
        'dthr_ch'
    ];

     public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    public function user()
    {
        return $this->setConnection('mysql')->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function rules($id = '')
    {
        return [
            'cliente_id' => 'required',
            'cliente_doc' => 'min:11|max:14|string|required',
            'status' => 'required',
            'cliente_dt_nasc' => 'required',
            'cliente_email' => 'required|email',
            'cliente_cel' => 'required',
            'user_id' => 'required',
            'date' => 'required',
            'start' => 'required',
            'end' => 'required',
            'agendamento_tipo' => 'required',
            'observacao' => 'max:500',
        ];
    }

    public function attributes()
    {
        return [
            'cliente_tipo' =>    'Tipo de Cliente',
            'cliente_sts' =>     'Status do Cliente',
            'cliente_doc' =>     'CPF/CNPJ',
            'cliente_pasprt' =>  'Número do Passaporte',
            'cliente_nome' =>    'Nome',
            'cliente_email' =>   'E-mail',
            'cliente_cel' =>     'Celular',
            'cliente_rendam' =>  'Renda Mensal Aprox.',
            'cliente_dt_fech' => 'Dia para Fech.',
        ];
    }

    public function messages()
    {
        return [
             'cliente_doc.unique' => 'Já existe um Cliente cadastrado com esse CPF/CNPJ.',

        ];
    }


}
