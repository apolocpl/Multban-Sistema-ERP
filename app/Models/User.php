<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Multban\DadosMestre\TbDmUserFunc;
use App\Models\Multban\DadosMestre\TbDmUserStatus;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\TbCf\ConexoesBcEmp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $connection = 'mysql';

    protected $table = 'tbsy_user';

    public $timestamps = false;

    public function getKeyName()
    {
        return 'user_id';
    }

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_email',
        'user_pass',
        'emp_id',
        'user_comis',
        'user_pcomis',
        'user_seller',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_pass',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'user_pass'         => 'hashed',
        ];
    }

    public function rules($id = '')
    {
        return [
            // Dados Gerais
            'emp_id'           => 'required',
            'user_logon'       => 'required|unique:tbsy_user,user_logon,' . $id . ',user_id',
            'user_cpf'         => 'required|unique:tbsy_user,user_cpf,' . $id . ',user_id',
            'user_sts'         => 'required',
            'user_name'        => 'required',
            'user_func'        => 'required',
            'user_email'       => 'required|email|unique:tbsy_user,user_email,' . $id . ',user_id',
            'confirm_password' => 'same:user_pass',
            'user_role'        => 'required',
            'user_screen'      => 'required',
            'user_cel'         => 'required',
            'user_agbc'        => 'required_with:user_cdgbc|max:20',
            'user_ccbc'        => 'required_with:user_cdgbc|max:20',
            'user_pix'         => 'required_with:user_cdgbc|max:20',
            'user_seller'      => 'required_with:user_cdgbc|max:20',
        ];
    }

    public function rulesCreate()
    {
        return [
            // Dados Gerais
            'emp_id'           => 'required',
            'user_logon'       => 'required|unique:tbsy_user,user_logon',
            'user_cpf'         => 'required|unique:tbsy_user,user_cpf',
            'user_sts'         => 'required',
            'user_name'        => 'required',
            'user_func'        => 'required',
            'user_pass'        => 'required',
            'user_email'       => 'required|email|unique:tbsy_user,user_email',
            'confirm_password' => 'required|same:user_pass',
            'user_role'        => 'required',
            'user_screen'      => 'required',
            'user_cel'         => 'required',
            'user_agbc'        => 'required_with:user_cdgbc|max:20',
            'user_ccbc'        => 'required_with:user_cdgbc|max:20',
            'user_pix'         => 'required_with:user_cdgbc|max:20',
            'user_seller'      => 'required_with:user_cdgbc|max:20',
        ];
    }

    public function attributes()
    {
        return [
            'emp_id'           => '"Dados Gerais"Empresa',
            'user_name'        => '"Dados Gerais"Nome Completo',
            'user_sts'         => '"Dados Gerais"Status',
            'user_logon'       => '"Dados Gerais"Usuário de Logon',
            'user_crm'         => 'CRM do Usuário',
            'user_email'       => '"Dados Gerais"E-mail',
            'user_func'        => '"Dados Gerais"Cargo',
            'user_cpf'         => '"Dados Gerais"CPF',
            'user_pass'        => '"Senhas"Senha do Usuário',
            'confirm_password' => '"Senhas"Repetir Senha',
            'user_role'        => '"Dados Gerais"Permissões',
            'user_cdgbc'       => '"Dados Adicionais"Banco Principal',
            'user_agbc'        => '"Dados Adicionais"Agência',
            'user_ccbc'        => '"Dados Adicionais"Conta Corrente',
            'user_pix'         => '"Dados Adicionais"Chave PIX',
            'user_seller'      => '"Dados Adicionais"Seller',
        ];
    }

    public function messages()
    {
        return [
            // 'name.required' => 'O campo :attribute não pode ficar em branco.',
            // 'user_sts.required' => 'O campo :attribute não pode ficar em branco.',
            // 'user_email.required' => 'O campo :attribute não pode ficar em branco.',
            // 'user_email.email' => 'O campo :attribute é inválido.',
            // 'user_email.unique' => 'Endereço de e-mail já cadastrado.',
            // 'user_pass.required' => 'O campo :attribute não pode ficar em branco.',
            // 'confirm_password.required' => 'O campo :attribute não pode ficar em branco.',
            // //'confirm_password.same' => 'As senhas não conferem',
            // 'user_role.required' => 'O campo :attribute não pode ficar em branco.',
            // 'user_logon.required' => 'O campo :attribute não pode ficar em branco.',
            // 'user_logon.unique' => 'Usuário de Logon já cadastrado.',
            // 'user_crm.required' => '"Dados Gerais"'
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function getEmpId()
    {
        return $this->emp_id;
    }

    public function getUserDataBase()
    {

        $conexao = ConexoesBcEmp::where('emp_id', $this->emp_id)->first();

        return $conexao;
    }

    public function getAuthPassword()
    {
        return $this->user_pass;
    }

    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    public function status()
    {
        return $this->belongsTo(TbDmUserStatus::class, 'user_sts', 'user_sts');
    }

    public function cargo()
    {
        return $this->belongsTo(TbDmUserFunc::class, 'user_func', 'user_func');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'emp_id', 'emp_id');
    }
}
