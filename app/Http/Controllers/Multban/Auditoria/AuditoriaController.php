<?php

namespace App\Http\Controllers\Multban\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Multban\Auditoria\LogAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AuditoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filtros = [0 => 'CÓDIGO', 1 => 'DESCRIÇÃO'];

        // return view('Multban.auditoria.index', compact('filtros'));
        return response()->view('Multban.auditoria.index', compact('filtros'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Not implemented yet
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function getObterGridPesquisa(Request $request)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $parametro = $request->parametro;
        // dd($request->all());
        $data = '';
        switch ($request->idFiltro) {
            case 0: // ProdutoConstans::codigo:
                if (! empty($parametro)) {
                    if (! empty($request->audarq)) {
                        $data = LogAuditoria::where('audarq', $request->audarq)->where('audlan', $parametro)->limit(100)->orderBy('id', 'desc')->get();
                    } else {

                        $data = LogAuditoria::where('id', $parametro)->limit(100)->get();
                    }
                } else {
                    $data = LogAuditoria::limit(100)->get();
                }
                break;
            case 1: // ProdutoConstans::titulo:
                if (! empty($parametro)) {
                    $data = LogAuditoria::where('fardes', 'LIKE', '%' . $parametro . '%')->limit(100)->get();
                } else {
                    $data = LogAuditoria::limit(100)->get();
                }
                break;
            default:
                break;
        }

        // $this->permissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('audant', function ($row) {
                $retorno = $row->audant;
                if (is_numeric($row->audant)) {
                    if (is_float($row->audant + 0)) {
                        $retorno = number_format($row->audant, 2);
                    }
                }

                return $retorno;
            })
            ->editColumn('auddat', function ($row) {
                return date('d/m/Y H:i:s', strtotime($row->auddat));
            })->make(true);
    }
}
