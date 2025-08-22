<?php

namespace App\Http\Controllers\Multban\Agendamento;

use App\Http\Controllers\Controller;
use App\Models\Multban\Agendamento\Agendamento;
use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\DadosMestre\TbDmAgendamentoStatus;
use App\Models\Multban\DadosMestre\TbDmAgendamentoTipo;
use App\Models\Multban\DadosMestre\TbDmConvenios;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AgendamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('Multban.agendamento.index');
    }

    public function getAgenda(Request $request)
    {
        try {
            $data = Agendamento::whereDate('start', '>=', $request->start)
                ->whereDate('end',   '<=', $request->end)
                ->get(['id', 'title', 'start', 'end', 'description', 'class_name as classNames', 'status', 'cliente_id', 'user_id', 'date', 'observacao', 'agendamento_tipo']);

            foreach ($data as $item) {
                $cliente = Cliente::find($item->cliente_id);
                $profissional = User::find($item->user_id);
                $agendamentoTipo = TbDmAgendamentoTipo::find($item->agendamento_tipo);
                $className = '';
                $status = '';
                $statusClass = '';
                $statusClassAg = '';
                $tipoClass = '';

                switch ($cliente->cliente_sts) {
                    case 'AT':
                        $statusClass = 'text-success';
                        break;
                    case 'BL':
                        $statusClass = 'text-danger';
                        break;
                    case 'EX':
                        $statusClass = 'text-danger';
                        break;
                    case 'ID':
                        $statusClass = 'text-warning';
                        break;
                    case 'IN':
                        $statusClass = 'text-danger';
                        break;
                    case 'NA':
                        $statusClass = 'text-warning';
                        break;
                }

                switch ($item->status) {
                    case 'AG':
                        $status = 'Agendado';
                        $className = 'text-left badge bg-primary w-100 m-0';
                        $statusClassAg = 'text-info';
                        break;
                    case 'CN':
                        $status = 'Cancelado';
                        $className = 'text-left badge bg-danger w-100 m-0';
                        $statusClassAg = 'text-danger';
                        break;
                    case 'NA':
                        $status = 'Não Compareceu';
                        $className = 'text-left badge bg-warning w-100 m-0';
                        $statusClassAg = 'text-warning';
                        break;
                    case 'RE':
                        $status = 'Realizado';
                        $className = 'text-left badge bg-success w-100 m-0';
                        $statusClassAg = 'text-success';
                        break;
                }

                if($item->agendamento_tipo == 4) {
                    $tipoClass = 'text-danger';
                } else {
                    $tipoClass = 'text-secondary';
                }

                $item->classNames = $className;
                $item->description = '<p>Paciente: ' . $cliente->cliente_nome . ' <br>Status Cliente: <span class="' . $statusClass . '">' . $cliente->status->cliente_sts_desc . '</span> <br>Profissional: ' . $profissional->user_name . ' - ' . $profissional->cargo->user_func_desc
                    . '<br>Data: ' . Carbon::createFromFormat('Y-m-d', $item->date)->format('d/m/Y') . ' <br> Horário: ' . Carbon::createFromFormat('Y-m-d H:i:s', $item->start)->format('H:i') . ' - ' . Carbon::createFromFormat('Y-m-d H:i:s', $item->end)->format('H:i') . ' <br> Tipo: <span class="' . $tipoClass . '">' . $agendamentoTipo->agendamento_tipo_desc . '</span> <br> Status consulta: <span class="' . $statusClassAg . '">' . $status . '</span> <p>Observação: ' . $item->observacao . '</p>';
            }


            return response()->json($data);
        } catch (Exception | \Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agendamento = new Agendamento();

        $status = TbDmAgendamentoStatus::all();
        $tipos = TbDmAgendamentoTipo::all();

        $dbsysclient = DB::connection('dbsysclient');
        $users = User::join($dbsysclient->getDatabaseName().'.tbdm_userfunc', 'tbsy_user.user_func', '=', 'tbdm_userfunc.user_func')
            ->where('user_func_grp', '=', 'consulta')->get();

        $convenios = TbDmConvenios::all();

        return view('Multban.agendamento.edit', compact('agendamento', 'status', 'tipos', 'users', 'convenios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $emp_id = Auth::user()->emp_id;
            $emp_id = Auth::user()->emp_id;

            $agendamento = new Agendamento();
            //dd($request->all());

            $validator = Validator::make($request->all(), $agendamento->rules(), $agendamento->messages(), $agendamento->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'message_type' => 'Verifique os campos obrigatórios e tente novamente.',

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $className = '';
            switch ($request->status) {
                case 'AG':
                    $className = 'text-left badge bg-primary w-100 m-0';
                    break;
                case 'CN':
                    $className = 'text-left badge bg-danger w-100 m-0';
                    break;
                case 'NA':
                    $className = 'text-left badge bg-warning w-100 m-0';
                    break;
                case 'RE':
                    $className = 'text-left badge bg-success w-100 m-0';
                    break;
            }

            $agendamento->class_name = $className;
            $agendamento->prontuario_id = 0;
            $agendamento->agendamento_tipo = $request->agendamento_tipo;
            $agendamento->cliente_id = $request->cliente_id;
            $agendamento->user_id = $request->user_id;
            $agendamento->description = $request->description;
            $agendamento->date = $request->date;
            $cliente = Cliente::find($request->cliente_id);

            if (!$cliente) {

                $cliente = new Cliente();
                $input = $request->all();

                $input['cliente_doc'] = removerCNPJ($request->cliente_doc);
                $input['cliente_tipo'] = 1;
                $input['cliente_sts'] = 'NA';
                $input['cliente_nome'] = $request->cliente_id;

                $clienteChk = Cliente::where('cliente_doc', removerCNPJ($request->cliente_doc))->first();
                if ($clienteChk) {
                    return response()->json([
                        'message_type' => 'Já existe um cliente cadastrado com esse CPF/CNPJ.',
                        'message' => ['cliente_doc' => ['Já existe um cliente cadastrado com esse CPF/CNPJ.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $clienteChkEmail = Cliente::where('cliente_email', $request->cliente_email)->first();
                if ($clienteChkEmail) {
                    return response()->json([
                        'message_type' => 'Já existe um cliente cadastrado com esse e-mail.',
                        'message' => ['cliente_email' => ['Já existe um cliente cadastrado com esse e-mail.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $validator = Validator::make($input, $cliente->rulesAgendamento(), $cliente->messages(), $cliente->attributes());

                if ($validator->fails()) {
                    return response()->json([
                        'message'   => $validator->errors(),

                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $cliente->cliente_tipo       = 1;
                $cliente->convenio_id        = $request->convenio_id;
                $cliente->carteirinha        = $request->nro_carteirinha;
                $cliente->cliente_rendam        = 0;
                $cliente->cliente_dt_fech        = 0;
                $cliente->cliente_dt_nasc    = $request->cliente_dt_nasc ? Carbon::createFromFormat('d/m/Y', $request->cliente_dt_nasc)->format('Y-m-d') : null;
                $cliente->cliente_doc        = removerCNPJ($request->cliente_doc);
                $cliente->cliente_rg         = removerCNPJ($request->cliente_rg);
                $cliente->cliente_sts        = 'NA'; /*Cliente nasce com o status "Em Análise"*/
                $cliente->cliente_uuid       = Str::uuid()->toString();
                $cliente->cliente_nome       = mb_strtoupper(rtrim($request->cliente_id), 'UTF-8');
                $cliente->cliente_email      = $request->cliente_email;
                $cliente->cliente_cel        = removerMascaraTelefone($request->cliente_cel);
                $cliente->cliente_telfixo    = removerMascaraTelefone($request->cliente_telfixo);
                $cliente->criador            = Auth::user()->user_id;
                $cliente->modificador        = Auth::user()->user_id;
                $cliente->dthr_cr            = Carbon::now();
                $cliente->dthr_ch            = Carbon::now();

                $cliente->save();
                $agendamento->cliente_id = $cliente->cliente_id;

                $tbdm_clientes_emp = DB::connection('dbsysclient')->table('tbdm_clientes_emp')->insert([
                    'emp_id' => $emp_id,
                    'cliente_id' => $cliente->cliente_id,
                    'cliente_uuid' => $cliente->cliente_uuid,
                    'cliente_doc' => removerCNPJ($cliente->cliente_doc),
                    'cliente_pasprt' => $cliente->cliente_pasprt,
                    'cad_liberado' => '',
                    'criador' => Auth::user()->user_id,
                    'dthr_cr' => Carbon::now(),
                    'modificador' => Auth::user()->user_id,
                    'dthr_ch' => Carbon::now(),
                ]);
            }

            $profissional = User::find($request->user_id);
            $agendamento->title = $cliente->cliente_nome . ' - ' . $profissional->cargo->user_func_desc;
            $agendamento->description = '';
            // Se o campo é apenas hora, combine com a data para criar o datetime
            $agendamento->start = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->start)->format('Y-m-d H:i:s');
            $agendamento->end = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->end)->format('Y-m-d H:i:s');

            $agendamento->observacao = $request->observacao;
            $agendamento->status = $request->status;
            $agendamento->modificador = Auth::user()->user_id;
            $agendamento->dthr_ch = now();
            $agendamento->save();
            DB::commit();

            return response()->json([
                'message'   => "Agendamento criado com sucesso.",
                'redirect' => route('agendamento.index'),
            ]);
        } catch (Exception | \Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message'   => $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $agendamento = Agendamento::find($id);

        if (!$agendamento) {
            return redirect()->route('agendamento.index')
                ->with('error', 'Agendamento não encontrado.');
        }

        $status = TbDmAgendamentoStatus::all();
        $tipos = TbDmAgendamentoTipo::all();
        $users = User::whereIn('user_func', [
            '1',
            '2',
            '5',
            '6',
            '7',
            '9',
            '10',
            '12',
            '13',
            '14',
            '15',
            '21',
        ])->get();

        $convenios = TbDmConvenios::all();

        //$agendamento->date = Carbon::createFromFormat('Y-m-d', $agendamento->date)->format('d/m/Y');
        $agendamento->start = Carbon::createFromFormat('Y-m-d H:i:s', $agendamento->start)->format('H:i');
        $agendamento->end = Carbon::createFromFormat('Y-m-d H:i:s', $agendamento->end)->format('H:i');

        return view('Multban.agendamento.edit', compact('agendamento', 'status', 'tipos', 'users', 'convenios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            DB::beginTransaction();
            $emp_id = Auth::user()->emp_id;

            $agendamento = Agendamento::find($id);
            //dd($request->all());

            $validator = Validator::make($request->all(), $agendamento->rules(), $agendamento->messages(), $agendamento->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'message_type' => 'Verifique os campos obrigatórios e tente novamente.',

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $className = '';
            switch ($request->status) {
                case 'AG':
                    $className = 'text-left badge bg-primary w-100 m-0';
                    break;
                case 'CN':
                    $className = 'text-left badge bg-danger w-100 m-0';
                    break;
                case 'NA':
                    $className = 'text-left badge bg-warning w-100 m-0';
                    break;
                case 'RE':
                    $className = 'text-left badge bg-success w-100 m-0';
                    break;
            }

            $agendamento->class_name = $className;
            $agendamento->prontuario_id = 0;
            $agendamento->agendamento_tipo = $request->agendamento_tipo;
            $agendamento->cliente_id = $request->cliente_id;
            $agendamento->user_id = $request->user_id;
            $agendamento->description = $request->description;
            $agendamento->date = $request->date;
            $cliente = Cliente::find($request->cliente_id);

            if (!$cliente) {

                $cliente = new Cliente();
                $input = $request->all();

                $input['cliente_doc'] = removerCNPJ($request->cliente_doc);
                $input['cliente_tipo'] = 1;
                $input['cliente_sts'] = 'NA';
                $input['cliente_nome'] = $request->cliente_id;

                $clienteChk = Cliente::where('cliente_doc', removerCNPJ($request->cliente_doc))->first();
                if ($clienteChk) {
                    return response()->json([
                        'message_type' => 'Já existe um cliente cadastrado com esse CPF/CNPJ.',
                        'message' => ['cliente_doc' => ['Já existe um cliente cadastrado com esse CPF/CNPJ.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $clienteChkEmail = Cliente::where('cliente_email', $request->cliente_email)->first();
                if ($clienteChkEmail) {
                    return response()->json([
                        'message_type' => 'Já existe um cliente cadastrado com esse e-mail.',
                        'message' => ['cliente_email' => ['Já existe um cliente cadastrado com esse e-mail.']],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $validator = Validator::make($input, $cliente->rulesAgendamento(), $cliente->messages(), $cliente->attributes());

                if ($validator->fails()) {
                    return response()->json([
                        'message'   => $validator->errors(),

                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $cliente->cliente_tipo       = 1;
                $cliente->convenio_id        = $request->convenio_id;
                $cliente->carteirinha        = $request->nro_carteirinha;
                $cliente->cliente_rendam        = 0;
                $cliente->cliente_dt_fech        = 0;
                $cliente->cliente_dt_nasc    = $request->cliente_dt_nasc ? Carbon::createFromFormat('d/m/Y', $request->cliente_dt_nasc)->format('Y-m-d') : null;
                $cliente->cliente_doc        = removerCNPJ($request->cliente_doc);
                $cliente->cliente_rg         = removerCNPJ($request->cliente_rg);
                $cliente->cliente_sts        = 'NA'; /*Cliente nasce com o status "Em Análise"*/
                $cliente->cliente_uuid       = Str::uuid()->toString();
                $cliente->cliente_nome       = mb_strtoupper(rtrim($request->cliente_id), 'UTF-8');
                $cliente->cliente_email      = $request->cliente_email;
                $cliente->cliente_cel        = removerMascaraTelefone($request->cliente_cel);
                $cliente->cliente_telfixo    = removerMascaraTelefone($request->cliente_telfixo);
                $cliente->criador            = Auth::user()->user_id;
                $cliente->modificador        = Auth::user()->user_id;
                $cliente->dthr_cr            = Carbon::now();
                $cliente->dthr_ch            = Carbon::now();

                $cliente->save();
                $agendamento->cliente_id = $cliente->cliente_id;

                $tbdm_clientes_emp = DB::connection('dbsysclient')->table('tbdm_clientes_emp')->insert([
                    'emp_id' => $emp_id,
                    'cliente_id' => $cliente->cliente_id,
                    'cliente_uuid' => $cliente->cliente_uuid,
                    'cliente_doc' => removerCNPJ($cliente->cliente_doc),
                    'cliente_pasprt' => $cliente->cliente_pasprt,
                    'cad_liberado' => '',
                    'criador' => Auth::user()->user_id,
                    'dthr_cr' => Carbon::now(),
                    'modificador' => Auth::user()->user_id,
                    'dthr_ch' => Carbon::now(),
                ]);
            }

            $profissional = User::find($request->user_id);
            $agendamento->title = $cliente->cliente_nome . ' - ' . $profissional->cargo->user_func_desc;
            $agendamento->description = '';
            $agendamento->start = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->start)->format('Y-m-d H:i:s');
            $agendamento->end = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->end)->format('Y-m-d H:i:s');

            $agendamento->observacao = $request->observacao;
            $agendamento->status = $request->status;
            $agendamento->modificador = Auth::user()->user_id;
            $agendamento->dthr_ch = now();
            $agendamento->save();
            DB::commit();

            return response()->json([
                'message'   => "Agendamento atualizado com sucesso.",
                'redirect' => route('agendamento.index'),
            ]);
        } catch (Exception | \Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getCliente(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'cliente_nome';

        if (empty($parametro)) {
            return [];
        }

        if (!empty($request->campo)) {
            $campo = $request->campo;
        }

        return Cliente::select(DB::raw('cliente_id as id, cliente_id,cliente_rg, cliente_dt_nasc, cliente_email,cliente_cel,cliente_telfixo,convenio_id,carteirinha, cliente_doc, UPPER(' . $campo . ') text'))
            ->whereRaw(DB::raw($campo . " LIKE '%" . $parametro . "%' OR cliente_id = '%" . $parametro . "%'"))
            ->get()
            ->toArray();
    }
}
