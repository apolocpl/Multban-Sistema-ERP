<?php

namespace App\Enums;

abstract class NotaFiscalEnum
{
    const TXT = 0;

    const STD = 1;

    const VERSAO = '4.00';

    const SIMPLESNACIONAL = 1;

    // Situação da nota fiscal
    const AUTORIZADA = '1';

    const INUTILIZADA = '2';

    const DENEGADA = '3';

    const CANCELADA = '4';

    const ESTORNADA = '5';

    const ENVIAR = '6';

    const REJEICAO = '7';

    const OUTROS = '8';

    const NAOENCONTRADA = '9';

    const TODAS = '10';
}
