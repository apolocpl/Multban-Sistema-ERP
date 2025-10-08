<?php

namespace App\Enums;

abstract class EmpresaStatusEnum
{
    const ATIVO = 'AT';

    const INATIVO = 'IN';

    const EXCLUIDO = 'EX';

    const BLOQUEADO = 'BL';

    const EMANALISE = 'NA';

    const INADIMPLENTE = 'IN';

    const ONBOARDING = 'ON';

    const ABERTURADECONTA = 'AC';

    const FATURADO = 'FT';
}
