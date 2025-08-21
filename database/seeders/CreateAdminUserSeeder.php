<?php

namespace Database\Seeders;

use App\Enums\EmpresaStatusEnum;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Empresa\EmpresaTaxpos;
use App\Models\Multban\TbCf\ConexoesBcEmp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'user_name' => 'Admin',
            'user_email' => 'admin@admin.com',
            'user_logon' => 'admin',
            'emp_id' => 1,
            'user_cpf' => '00000000000',
            'user_sts' => 'AT',
            'user_pass' => Hash::make('12345678'),
            'dthr_cr' => date_create()
        ]);

        $user = User::create([
            'user_name' => 'Medico',
            'user_email' => 'medico@admin.com',
            'user_logon' => 'medico',
            'user_func' => 12,
            'emp_id' => 1,
            'user_cpf' => '00000000001',
            'user_sts' => 'AT',
            'user_pass' => Hash::make('12345678'),
            'dthr_cr' => date_create()
        ]);

        $conexão = ConexoesBcEmp::create([
            'emp_id' => $user->emp_id,
            'bc_emp_host' => Crypt::encryptString('localhost'),
            'bc_emp_porta' => Crypt::encryptString('3306'),
            'bc_emp_nome' => Crypt::encryptString('db_sys_client'),
            'bc_emp_user' => Crypt::encryptString('root'),
            'bc_emp_pass' => Crypt::encryptString('eliton01'),
            'bc_fornec' => 'DGO'
        ]);

        $role = Role::create(['name' => 'admin', 'emp_id' => 1]);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
