<?php

namespace App\Http\Controllers\Multban\Produto;

use App\Http\Controllers\Controller;
use App\Models\Multban\Produto\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Multban.produto.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    $produto = new Produto();
    $tipos = \App\Models\Multban\Produto\ProdutoTipo::all();
    $status = \App\Models\Multban\Produto\ProdutoStatus::all();
    return view('Multban.produto.edit', compact('produto', 'tipos', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc' => 'required',
            'produto_dm' => 'required',
            'produto_dl' => 'required',
            'produto_vlr' => 'required',
            'produto_tipo' => 'required',
            'produto_sts' => 'required',
        ];
        if ($request->input('produto_tipo') == '1') {
            $rules['produto_ncm'] = 'required';
            $rules['produto_peso'] = 'required';
        }
        if ($request->input('produto_tipo') == '3') {
            $rules['partcp_pvlaor'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgsplit') {
            $rules['partcp_seller'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgtransf') {
            $rules['partcp_cdgbc'] = 'required';
            $rules['partcp_agbc'] = 'required';
            $rules['partcp_ccbc'] = 'required';
            $rules['partcp_pix'] = 'required';
        }
        $validated = $request->validate($rules);

        $data = $request->except(['_token', '_method']);
        $data['produto_ctrl'] = isset($data['produto_ctrl']) ? 'X' : '';
        $data['emp_id'] = $user->emp_id;
        if (isset($data['produto_vlr'])) {
            $data['produto_vlr'] = str_replace(['.', ','], ['', '.'], $data['produto_vlr']);
        }
        if (isset($data['produto_peso'])) {
            $data['produto_peso'] = str_replace([','], ['.'], $data['produto_peso']);
        }
        $data['criador'] = $user->user_id;
        $data['modificador'] = $user->user_id;
        $data['dthr_cr'] = now();
        $data['dthr_ch'] = now();
        $produto = Produto::create($data);
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Produto cadastrado com sucesso!',
                'redirect' => route('produtos.create')
            ]);
        }
        return redirect()->route('produtos.create')
            ->with('success', 'Produto cadastrado com sucesso!');
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
    $produto = Produto::find($id);
    $tipos = \App\Models\Multban\Produto\ProdutoTipo::all();
    $status = \App\Models\Multban\Produto\ProdutoStatus::all();
    return view('Multban.produto.edit', compact('produto', 'tipos', 'status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc' => 'required',
            'produto_dm' => 'required',
            'produto_dl' => 'required',
            'produto_vlr' => 'required',
            'produto_tipo' => 'required',
            'produto_sts' => 'required',
        ];
        if ($request->input('produto_tipo') == '1') {
            $rules['produto_ncm'] = 'required';
            $rules['produto_peso'] = 'required';
        }
        if ($request->input('produto_tipo') == '3') {
            $rules['partcp_pvlaor'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgsplit') {
            $rules['partcp_seller'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgtransf') {
            $rules['partcp_cdgbc'] = 'required';
            $rules['partcp_agbc'] = 'required';
            $rules['partcp_ccbc'] = 'required';
            $rules['partcp_pix'] = 'required';
        }
        $validated = $request->validate($rules);

        $data = $request->except(['_token', '_method']);
        $data['produto_ctrl'] = isset($data['produto_ctrl']) ? 'X' : '';
        $data['emp_id'] = $user->emp_id;
        if (isset($data['produto_vlr'])) {
            $data['produto_vlr'] = str_replace(['.', ','], ['', '.'], $data['produto_vlr']);
        }
        if (isset($data['produto_peso'])) {
            $data['produto_peso'] = str_replace([','], ['.'], $data['produto_peso']);
        }
        $data['modificador'] = $user->user_id;
        $data['dthr_ch'] = now();
        $produto = Produto::findOrFail($id);
        $produto->update($data);
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Produto atualizado com sucesso!',
                'redirect' => route('produtos.edit', $produto->produto_id)
            ]);
        }
        return redirect()->route('produtos.edit', $produto->produto_id)
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
