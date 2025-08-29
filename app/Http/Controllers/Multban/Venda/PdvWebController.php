<?php

namespace App\Http\Controllers\Multban\Venda;

use App\Http\Controllers\Multban\Produto\ProdutoController;
use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\MeioDePagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Multban\Venda\RegrasParc;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Produto\Produto;
use App\Models\Multban\Produto\ProdutoStatus;
use App\Models\Multban\Produto\ProdutoTipo;

class PdvWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $RegrasParc = RegrasParc::all();
        $meioDePagamento = MeioDePagamento::orderBy('meio_order')->get();

        // Obtém o emp_id do usuário autenticado
        $empId = Auth::user()->emp_id;

        // Filtra produtos pelo emp_id do usuário logado
        $produtos = Produto::where('emp_id', $empId)->get();

        // Carrega parâmetros da empresa para este emp_id
        $empresaParam = EmpresaParam::find($empId);

        // Indexa o array pelo código do status para facilitar busca no Blade
        $produtosStatus = ProdutoStatus::all()->keyBy('produto_sts');

        // Monta array de código => descrição
        $produtosTipo = ProdutoTipo::all()->pluck('produto_tipo_desc', 'produto_tipo');
        return view('Multban.venda.pdv.index', compact('meioDePagamento', 'RegrasParc', 'produtos', 'produtosStatus', 'produtosTipo', 'empresaParam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function pdv()
    {
        // Busca todas as regras de parcelamento
        // $RegrasParc = RegrasParc::all();
        // return view('Multban.venda.pdv.index', compact('RegrasParc'));
    }

}
