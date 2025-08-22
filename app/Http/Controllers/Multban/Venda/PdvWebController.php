<?php

namespace App\Http\Controllers\Multban\Venda;

use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\MeioDePagamento;
use Illuminate\Http\Request;
use App\Models\Multban\Venda\RegrasParc;

class PdvWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $RegrasParc = RegrasParc::all();
        $meioDePagamento = MeioDePagamento::orderBy('meio_order')->get();
        return view('Multban.venda.pdv.index', compact('meioDePagamento', 'RegrasParc'));
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
        $RegrasParc = RegrasParc::all();

        // ... outros dados necessários para a view

        return view('Multban.venda.pdv.index', compact('RegrasParc', /* outros dados */));
    }
}
