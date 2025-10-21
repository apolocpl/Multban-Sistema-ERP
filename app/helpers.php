<?php

use Carbon\Carbon;

if (!function_exists('tirarAcentos')) {
    function tirarAcentos($string)
    {
        return preg_replace(['/(ç)/', '/(Ç)/', '/(á|à|ã|â|ä)/', '/(Á|À|Ã|Â|Ä)/', '/(é|è|ê|ë)/', '/(É|È|Ê|Ë)/', '/(í|ì|î|ï)/', '/(Í|Ì|Î|Ï)/', '/(ó|ò|õ|ô|ö)/', '/(Ó|Ò|Õ|Ô|Ö)/', '/(ú|ù|û|ü)/', '/(Ú|Ù|Û|Ü)/', '/(ñ)/', '/(Ñ)/'], explode(' ', 'c C a A e E i I o O u U n N'), $string);
    }
}

if (!function_exists('replicate')) {
    function replicate($expressao, $quantidade)
    {
        $result = '';
        for ($i = 1; $i < $quantidade; $i++) {
            $result .= $expressao;
        }
        return $result;
    }
}

if (!function_exists('in_array_r')) {
    function in_array_r($array, $field, $find)
    {
        foreach ($array as $item) {
            if ($item[$field] == $find) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('in')) {
    function in($valor, $comparador = [])
    {
        return in_array($valor, $comparador);
    }
}

if (!function_exists('formatarParaMoeda')) {
    function formatarParaMoeda($valor)
    {
        return sprintf('R$ %s', number_format($valor, 2, ',', '.'));
    }
}

if (!function_exists('formatarParaQuantidade')) {
    function formatarParaQuantidade($valor)
    {
        return sprintf('%s', number_format($valor, 2, ',', '.'));
    }
}

if (!function_exists('formatarDecimalParaTexto')) {
    function formatarDecimalParaTexto($valor)
    {
        return sprintf('%s', number_format($valor, 2, ',', '.'));
    }
}

if (!function_exists('formatarTextoParaDecimal')) {
    function formatarTextoParaDecimal($valor)
    {
        $valor = str_replace(['%', 'R$', '.', ','], ['', '', '', '.'], $valor);
        return $valor > 0 ? number_format((float) $valor, 2, '.', '') : null;
    }
}

if (!function_exists('formatarMoneyToDecimal')) {
    function formatarMoneyToDecimal($valor)
    {
        return str_replace(['R$', ','], ['', '.'], str_replace('.', '', $valor));
    }
}

if (!function_exists('removerVirgulaPorPonto')) {
    function removerVirgulaPorPonto($valor)
    {
        return str_replace(',', '.', $valor);
    }
}

if (!function_exists('numero')) {
    function numero($valor)
    {
        return number_format(round((float) ($valor), 4), 2);
    }
}

if (!function_exists('formatarParaMoedaDecimal')) {
    function formatarParaMoedaDecimal($valor)
    {
        return sprintf('%s', number_format($valor, 2, ',', ''));
    }
}

if (!function_exists('adicionarCodigoEDescricao')) {
    function adicionarCodigoEDescricao($model, $idCampo = 'id', $campo = 'descricao')
    {
        $retorno = '';
        if ($model != null) {
            if ($model != null && $model[$idCampo]) {
                $retorno = $model[$idCampo] . ' - ' . strtoupper($model[$campo]);
            } elseif ($model['descricao']) {
                $retorno = $model['id'] . ' - ' . strtoupper($model['descricao']);
            } else {
                $retorno = $model['id'] . ' - ' . strtoupper($model['razaosocial']);
            }
        }
        return $retorno;
    }
}

if (!function_exists('codigoEDescricaoCliente')) {
    function codigoEDescricaoCliente($model)
    {
        $retorno = '';
        if ($model != null && $model['id']) {
            $retorno = $model['id'] . ' - ' . strtoupper($model['razaosocial']);
        }
        return $retorno;
    }
}

if (!function_exists('formatarData')) {
    function formatarData($data, $formatoData = 'Y-m-d H:i:s', $formato = 'd/m/Y')
    {
        return Carbon::createFromFormat($formatoData, $data)->format($formato);
    }
}

if (!function_exists('formatarDataComHora')) {
    function formatarDataComHora($date, $date_format = 'd/m/Y H:i:s')
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($date_format);
    }
}

if (!function_exists('formatarDataDes')) {
    function formatarDataDes($data, $formato = 'd M Y H:m')
    {
        return date($formato, strtotime($data));
    }
}

if (!function_exists('stringZero')) {
    function stringZero($string, $valorStr = 6)
    {
        return str_pad('' . $string, $valorStr, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('mascaraGenerica')) {
    function mascaraGenerica($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }
}

if (!function_exists('removerCNPJ')) {
    function removerCNPJ($cnpj)
    {
        return preg_replace("/\D+/", '', $cnpj);
    }
}

if (!function_exists('removerMascaraIE')) {
    function removerMascaraIE($ie)
    {
        return preg_replace("/\D+/", '', $ie);
    }
}

if (!function_exists('removerMascaraCEP')) {
    function removerMascaraCEP($cep)
    {
        return str_replace('-', '', $cep);
    }
}

if (!function_exists('removerMascaraTelefone')) {
    function removerMascaraTelefone($telefone)
    {
        return preg_replace("/[\(\)\.\s-]+/", '', $telefone);
    }
}

if (!function_exists('formatarCNPJCPF')) {
    function formatarCNPJCPF($cnpj_cpf)
    {
        $cnpj_cpf = removerMascaraTelefone($cnpj_cpf);
        return strlen($cnpj_cpf) == 11 ? mascaraGenerica($cnpj_cpf, '###.###.###-##') : mascaraGenerica($cnpj_cpf, '##.###.###/####-##');
    }
}

if (!function_exists('formatarCNPJ')) {
    function formatarCNPJ($cnpj)
    {
        return mascaraGenerica($cnpj, '##.###.###/####-##');
    }
}

if (!function_exists('formatarCartaoCredito')) {
    function formatarCartaoCredito($card)
    {
        return mascaraGenerica($card, '#### #### #### ####');
    }
}

if (!function_exists('formatarTelefone')) {
    function formatarTelefone($phone)
    {
        $phone = removerMascaraTelefone($phone);
        return strlen($phone) == 11 ? mascaraGenerica($phone, '(##) #####-####') : mascaraGenerica($phone, '(##) ####-####');
    }
}

if (!function_exists('formatarCPF')) {
    function formatarCPF($cpf)
    {
        return mascaraGenerica($cpf, '###.###.###-##');
    }
}

if (!function_exists('valida_cnpj')) {
    function valida_cnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        if (strlen($cnpj) != 14) {
            return false;
        }
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}

if (!function_exists('valida_cpf')) {
    function valida_cpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        if (strlen($cpf) != 11) {
            return false;
        }
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('selectItens')) {
    function selectItens($Lista = [], $Campo = '', $Dig = 0)
    {
        $Ret = '';
        for ($i = 0; $i < count((array) $Lista); $i++) {
            if (empty($Lista[$i])) {
                if (empty($Ret)) {
                    $Ret .= ' AND ' . $Campo . " in('" . $Lista[$i] . "',";
                } else {
                    $Ret .= $Lista[$i] . "',";
                }
            } else {
                if ($Dig > 0) {
                    if (empty($Ret)) {
                        $Ret .= $Campo . " in('" . $Lista[$i] . "',";
                    } else {
                        $Ret .= "'" . $Lista[$i] . "',";
                    }
                } else {
                    if (empty($Ret)) {
                        $Ret .= $Campo . " in('" . $Lista[$i] . "',";
                    } else {
                        $Ret .= "'" . $Lista[$i] . "',";
                    }
                }
            }
        }
        if (empty($Ret)) {
            $Ret = 'AND ' . $Campo . " in('')";
        } else {
            $Ret = substr($Ret, 0, strlen($Ret) - 3) . "')";
        }
        return $Ret;
    }
}

if (!function_exists('xml_attribute')) {
    function xml_attribute($object, $attribute)
    {
        if (isset($object[$attribute])) {
            return (string) $object[$attribute];
        }
    }
}

if (!function_exists('returnZero')) {
    function returnZero($number)
    {
        if ($number < 0) {
            return 0;
        }
        return $number;
    }
}

if (!function_exists('space')) {
    function space($qtd)
    {
        $string = '';
        for ($i = 0; $i < intval($qtd); $i++) {
            $string .= ' ';
        }
        return $string;
    }
}

if (!function_exists('quotedstr')) {
    function quotedstr($string)
    {
        return "'" . $string . "'";
    }
}
