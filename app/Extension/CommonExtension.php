<?php

namespace App\Extension;

class CommonExtension
{
    public static function tirarAcentos($string)
    {
        return preg_replace(['/(ç)/', '/(Ç)/', '/(á|à|ã|â|ä)/', '/(Á|À|Ã|Â|Ä)/', '/(é|è|ê|ë)/', '/(É|È|Ê|Ë)/', '/(í|ì|î|ï)/', '/(Í|Ì|Î|Ï)/', '/(ó|ò|õ|ô|ö)/', '/(Ó|Ò|Õ|Ô|Ö)/', '/(ú|ù|û|ü)/', '/(Ú|Ù|Û|Ü)/', '/(ñ)/', '/(Ñ)/'], explode(' ', 'c C a A e E i I o O u U n N'), $string);
    }

    public static function in_array_r_key($array, $field, $find)
    {
        foreach ($array as $key => $item) {
            if ($item[$field] == $find) {
                return ['retorno' => true, 'key' => $key];
            }
        }

        return ['retorno' => false, 'key' => ''];
    }

    public static function in_array_r($array, $field, $find)
    {
        foreach ($array as $item) {
            if ($item[$field] == $find) {
                return true;
            }
        }

        return false;
    }

    public static function in($valor, $comparador = [])
    {
        return in_array($valor, $comparador);
    }

    public static function formatarParaMoeda($valor)
    {
        return sprintf('R$ %s', number_format($valor, 2, ',', '.'));
    }

    public static function formatarParaQuantidade($valor)
    {
        return sprintf('%s', number_format($valor, 2, ',', '.'));
    }

    public static function formatarTextoParaDecimal($valor)
    {
        return str_replace(',', '.', str_replace('.', '', $valor));
    }

    public static function formatarMoneyToDecimal($valor)
    {
        return str_replace(['R$', ','], ['', '.'], str_replace('.', '', $valor));
    }

    public static function removerVirgulaPorPonto($valor)
    {
        return str_replace(',', '.', $valor);
    }

    public static function numero($valor)
    {
        return number_format(round((float) ($valor), 4), 2);
    }

    public static function formatarParaMoedaDecimal($valor)
    {
        return sprintf('%s', number_format($valor, 2, ',', ''));
    }

    public static function adicionarCodigoEDescricao($model, $idCampo = 'id', $campo = 'descricao')
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

    public static function codigoEDescricaoCliente($model)
    {
        $retorno = '';
        if ($model != null && $model['id']) {
            $retorno = $model['id'] . ' - ' . strtoupper($model['razaosocial']);
        }

        return $retorno;
    }

    public static function formatarData($data, $formato = 'd/m/Y')
    {
        return date_format($data, $formato);
    }

    public static function formatarDataComHora($data, $formato = 'd/m/Y H:i:s')
    {
        return date($formato, strtotime($data));
    }

    public static function formatarDataDes($data, $formato = 'd M Y H:m')
    {
        return date($formato, strtotime($data));
    }

    public static function stringZero($string, $valorStr = 6)
    {
        $quantidade = strlen($string);

        return str_pad('' . $string, $valorStr, '0', STR_PAD_LEFT);
    }

    public static function mascaraGenerica($val, $mask)
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

    public static function removerCNPJ($cnpj)
    {
        return preg_replace("/\D+/", '', $cnpj);
    }

    public static function removerMascaraIE($ie)
    {
        return preg_replace("/\D+/", '', $ie);
    }

    public static function removerMascaraCEP($cep)
    {
        return str_replace('-', '', $cep);
    }

    public static function removerMascaraTelefone($telefone)
    {
        return preg_replace("/[\(\)\.\s-]+/", '', $telefone);
    }

    public static function formatarCNPJ($cnpj)
    {
        return CommonExtension::mascaraGenerica($cnpj, '##.###.###/####-##');
    }

    public static function formatarTelefone($phone)
    {
        $phone = CommonExtension::removerMascaraTelefone($phone);

        return strlen($phone) == 11 ? CommonExtension::mascaraGenerica($phone, '(##) #####-####') : CommonExtension::mascaraGenerica($phone, '(##) ####-####');
    }

    public static function formatarCPF($cpf)
    {
        return CommonExtension::mascaraGenerica($cpf, '###.###.###-##');
    }

    public static function valida_cnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    public static function valida_cpf($cpf)
    {

        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
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
