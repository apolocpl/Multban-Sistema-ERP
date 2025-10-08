<?php

namespace App\Http\Controllers\Multban\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\Multban\TbCf\TbCfMsgComp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracoesController extends Controller
{
    public function getMensagensComprovante(Request $request)
    {
        $emp_id = Auth::user()->emp_id;
        $canal_id = $request->input('canal_id');
        $categorias = $request->input('categorias');
        if (! is_array($categorias)) {
            $categorias = explode(',', $categorias);
        }

        $mensagens = TbCfMsgComp::where('emp_id', $emp_id)
            ->where('canal_id', $canal_id)
            ->whereIn('msg_categ', $categorias)
            ->pluck('msg_text', 'msg_categ')
            ->toArray();

        if (count($mensagens) < count($categorias)) {
            $faltantes = array_diff($categorias, array_keys($mensagens));
            if (count($faltantes)) {
                $mensagens_padrao = TbCfMsgComp::where('emp_id', 1)
                    ->where('canal_id', $canal_id)
                    ->whereIn('msg_categ', $faltantes)
                    ->pluck('msg_text', 'msg_categ')
                    ->toArray();
                $mensagens = array_merge($mensagens, $mensagens_padrao);
            }
        }

        return response()->json($mensagens);
    }
}
