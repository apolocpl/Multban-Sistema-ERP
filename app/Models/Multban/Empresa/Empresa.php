<?php

namespace App\Models\Multban\Empresa;

use App\Models\Multban\Endereco\Cidade;
use App\Models\Multban\Endereco\Estados;
use App\Models\Multban\Endereco\Pais;
use App\Models\Multban\TbCf\ConexoesBcEmp;
use App\Models\Multban\TbCf\ConfigWhiteLabel;
use App\Models\Multban\Traits\DbSysClientTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use DbSysClientTrait, HasFactory;

    protected $table = 'u630533599_dmb_db_sys_cli.tbdm_empresa_geral';

    public $timestamps = false;

    public function getKeyName()
    {
        return 'emp_id';
    }

    protected $primaryKey = 'emp_id';

    public function conexoesBcEmp()
    {
        return $this->belongsTo(ConexoesBcEmp::class, 'emp_id');
    }

    public function empresaParam()
    {
        return $this->belongsTo(EmpresaParam::class, 'emp_id', 'emp_id');
    }

    public function empresaTaxpos()
    {
        return $this->hasMany(EmpresaTaxpos::class, 'emp_id', 'emp_id');
    }

    public function status()
    {
        return $this->belongsTo(EmpresaStatus::class, 'emp_sts', 'emp_sts');
    }

    public function ramodeatividade()
    {
        return $this->belongsTo(EmpresaRamoDeAtividade::class, 'emp_ratv');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'emp_endpais', 'pais');
    }

    public function estado()
    {
        return $this->belongsTo(Estados::class, 'emp_endest');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'emp_endcid', 'cidade');
    }

    public function whiteLabel()
    {
        return $this->belongsTo(ConfigWhiteLabel::class, 'emp_id', 'emp_id');
    }

    public function rules($id = '')
    {
        return [
            'emp_rzsoc'   => 'required|max:255',
            'emp_cnpj'    => 'min:14|max:14|string|required|unique:tbdm_empresa_geral,emp_cnpj, ' . $id . ',emp_id',
            'emp_sts'     => 'required',
            'emp_nfant'   => 'required|max:255',
            'emp_nmult'   => 'required|max:15',
            'vlr_imp'     => 'required',
            'dtvenc_imp'  => 'required',
            'cond_pgto'   => 'required',
            'vlr_mens'    => 'required',
            'dtvenc_mens' => 'required',
            'emp_tpbolet' => 'required_with:emp_checkb',
            'tp_plano'    => 'required_with:emp_checkm',
            'emp_adqrnt'  => 'required_with:emp_checkc',
            'emp_meiocom' => 'required',
            // endereço
            'emp_cep'     => 'min:8|max:8|required',
            'emp_end'     => 'required',
            'emp_endnum'  => 'required',
            'emp_endbair' => 'required',
            'emp_endcid'  => 'required',
            'emp_endest'  => 'required',
            'emp_endpais' => 'required',
            // Contatos
            'emp_resplg'  => 'required',
            'emp_emailrp' => 'required|email',
            'emp_celrp'   => 'required|min:11|max:11',
            'emp_respcm'  => 'required',
            'emp_emailcm' => 'required|email',
            'emp_celcm'   => 'required|min:11|max:11',
            'emp_respfi'  => 'required',
            'emp_emailfi' => 'required|email',
            'emp_celfi'   => 'required|min:11|max:11',
            'emp_pagweb'  => 'required',
            'emp_rdsoc'   => 'required',
            // multban
            'emp_cdgbc'  => 'required',
            'emp_agbc'   => 'required_with:emp_cdgbc|max:20',
            'emp_ccbc'   => 'required_with:emp_cdgbc',
            'emp_pix'    => 'required_with:emp_cdgbc',
            'emp_seller' => 'required_with:emp_cdgbc',
            // 'emp_cdgbcs' => 'Banco Principal',
            'emp_agbcs'      => 'required_with:emp_cdgbcs|max:20',
            'emp_ccbcs'      => 'required_with:emp_cdgbcs',
            'emp_pixs'       => 'required_with:emp_cdgbcs',
            'emp_sellers'    => 'required_with:emp_cdgbcs',
            'qtde_cns_freem' => 'required_with:lib_cnscore',
            'qtde_cns_cntrm' => 'required_with:lib_cnscore',
            // 'qtde_cns_prem' => 'required_with:lib_cnscore',
            'tax_blt'        => 'required_with:blt_ctr',
            'vlr_pix'        => 'required',
            'vlr_boleto'     => 'required',
            'vlr_bolepix'    => 'required',
            'dias_inat_card' => 'required',
            'isnt_pixblt'    => 'required',
            'card_posparc'   => 'required_with:card_posctr',
            'perc_mlt_atr'   => 'required_with:cob_mltjr_atr',
            'perc_jrs_atr'   => 'required_with:cob_mltjr_atr',
            'perc_com_mltjr' => 'required_with:cob_mltjr_atr',
            'parc_jr_deprc'  => 'required_with:parc_cjuros',
            'tax_jrsparc'    => 'required_with:parc_cjuros',
            'parc_com_jrs'   => 'required_with:parc_cjuros',
            'tax_pre'        => 'required_with:card_prectr',
            'tax_gift'       => 'required_with:card_giftctr',
            'tax_fid'        => 'required_with:card_fidctr',
            // Antecipação
            'tax_antmult'    => 'required_with:antecip_ctr',
            'tax_antfundo'   => 'required_with:antecip_ctr',
            'perc_rec_ant'   => 'required_with:antecip_ctr',
            'ant_auto_srvd'  => 'required_with:antecip_auto',
            'ant_auto_prdvo' => 'required_with:antecip_auto',
            'ant_auto_prdvd' => 'required_with:antecip_auto',
            'fndant_agbc'    => 'required_with:fndant_cdgbc|max:20',
            'fndant_ccbc'    => 'required_with:fndant_cdgbc',
            'fndant_pix'     => 'required_with:fndant_cdgbc',
            'fndant_seller'  => 'required_with:fndant_cdgbc',
            // Rebate / Royalties / Comissão
            'tax_rebate'    => 'required_with:rebate_emp',
            'tax_royalties' => 'required_with:royalties_emp',
            'tax_comiss'    => 'required_with:comiss_emp',
            // Cobranças
            'cobsrv_diasatr'  => 'required_with:cobsrv_atv',
            'cobsrv_multa'    => 'required_with:cobsrv_atv',
            'cobsrv_juros'    => 'required_with:cobsrv_atv',
            'tax_cobsrv_adm'  => 'required_with:cobsrv_atv',
            'tax_cobsrv_juss' => 'required_with:cobsrv_atv',
        ];
    }

    public function attributes()
    {
        return [
            'emp_cnpj'    => '"Dados Gerais" CNPJ',
            'emp_sts'     => '"Dados Gerais" Status da Empresa',
            'emp_wl'      => '"Dados Gerais" Contrato White Label',
            'emp_wlde'    => '"Dados Gerais"White Label da Empresa',
            'emp_comwl'   => '"Dados Gerais"Comissionamento White Label',
            'emp_privlbl' => '"Dados Gerais"Contrato Private Label',
            'emp_ie'      => '"Dados Gerais"Inscrição Estadual',
            'emp_im'      => '"Dados Gerais"Inscrição Municipal',
            'emp_rzsoc'   => '"Dados Gerais"Razão Social',
            'emp_nfant'   => '"Dados Gerais"Nome Fantasia',
            'emp_nmult'   => '"Dados Gerais"Nome multban',
            'emp_ratv'    => '"Dados Gerais"Ramo de atividade',
            'emp_frqmst'  => '"Dados Gerais"Franqueado Master',
            'emp_frq'     => '"Dados Gerais"Empresa é Franqueadora',
            'emp_frqcmp'  => '"Dados Gerais"Compartilha Cadastro com Franqueador',
            'emp_altlmt'  => '"Dados Gerais"Alterar Limite de Crédito',
            'tp_plano'    => '"Dados Gerais"Plano Contratado',
            'vlr_imp'     => '"Dados Gerais"Valor da Implantação',
            'dtvenc_imp'  => '"Dados Gerais"Data de Vencimento da Implantação',
            'cond_pgto'   => '"Dados Gerais"Condição de pagamento: Parcelas',
            'vlr_mens'    => '"Dados Gerais"Valor da Mensalidade',
            'dtvenc_mens' => '"Dados Gerais"Data de Vencimento da Mensalidade',
            'emp_tpbolet' => '"Dados Gerais"Tipo Boletagem',
            'emp_checkb'  => '"Dados Gerais"Check Out Boletagem',
            'emp_checkm'  => '"Dados Gerais"Check Out multban',
            'emp_checkc'  => '"Dados Gerais"Check Out Convencional',
            'emp_adqrnt'  => '"Dados Gerais"Adquirente',
            'emp_meiocom' => '"Dados Gerais"Como conheceu a Multban',
            // Endereço
            'emp_cep'     => '"Endereço"CEP',
            'emp_end'     => '"Endereço"Endereço',
            'emp_endnum'  => '"Endereço"Número',
            'emp_endcmp'  => '"Endereço"Complemento',
            'emp_endbair' => '"Endereço"Bairro',
            'emp_endcid'  => '"Endereço"Cidade',
            'emp_endest'  => '"Endereço"Estado',
            'emp_endpais' => '"Endereço"País',
            // Contatos
            'emp_resplg'  => '"Contatos"Responsável Legal',
            'emp_emailrp' => '"Contatos"Email Responsável Legal',
            'emp_celrp'   => '"Contatos"Telefone Responsável Legal',
            'emp_respcm'  => '"Contatos"Contato Comercial',
            'emp_emailcm' => '"Contatos"Email Comercial',
            'emp_celcm'   => '"Contatos"Telefone Comercial',
            'emp_respfi'  => '"Contatos"Contato Financeiro',
            'emp_emailfi' => '"Contatos"Email Financeiro',
            'emp_celfi'   => '"Contatos"Telefone Financeiro',
            'criador'     => 'Criado Por',
            'dthr_cr'     => 'Data e Hora da Criação',
            'modificador' => 'Modificado Por',
            'dthr_ch'     => 'Data e Hora da Modificação',
            // multban
            'blt_ctr'        => 'Boletagem',
            'tax_blt'        => '"multban"Taxa',
            'emp_cdgbc'      => '"multban"Banco Principal',
            'emp_agbc'       => '"multban"Agência',
            'emp_ccbc'       => '"multban"Conta Corrente',
            'emp_pix'        => '"multban"Chave PIX',
            'emp_seller'     => '"multban"Seller',
            'emp_cdgbcs'     => '"multban"Banco Secundário',
            'emp_agbcs'      => '"multban"Agência',
            'emp_ccbcs'      => '"multban"Conta Corrente',
            'emp_pixs'       => '"multban"Chave PIX',
            'emp_sellers'    => '"multban"Seller',
            'lib_cnscore'    => 'Liberar Consulta de SCORE',
            'qtde_cns_freem' => '',
            'qtde_cns_cntrm' => '',
            'qtde_cns_prem'  => '',
            'vlr_pix'        => '"multban"',
            'vlr_boleto'     => '"multban"',
            'vlr_bolepix'    => '"multban"',
            'dias_inat_card' => '"multban"',
            'isnt_pixblt'    => '"multban"',
            'card_posparc'   => '',
            'card_posctr'    => 'Cartão Pós Pago Contratado',
            'cob_mltjr_atr'  => 'Cobrar Multa e Juros por Atraso',
            'perc_mlt_atr'   => '',
            'perc_jrs_atr'   => '',
            'perc_com_mltjr' => '',
            'parc_cjuros'    => 'Parcelamento com Juros',
            'parc_jr_deprc'  => '',
            'tax_jrsparc'    => '',
            'parc_com_jrs'   => '',
            'tax_pre'        => '',
            'tax_gift'       => '',
            'tax_fid'        => '',
            'card_prectr'    => 'Cartão Pré Pago Contratado',
            'card_giftctr'   => 'Gift Card Contratado',
            // Antecipação
            'antecip_ctr'    => 'Antecipação Contratada',
            'tax_antmult'    => '"Antecipação"',
            'tax_antfundo'   => '"Antecipação"',
            'perc_rec_ant'   => '"Antecipação"',
            'antecip_auto'   => 'Antecipação Automática',
            'ant_auto_srvd'  => '"Antecipação"',
            'ant_auto_prdvo' => '"Antecipação"',
            'ant_auto_prdvd' => '"Antecipação"',
            'fndant_cdgbc'   => 'Cdg Banco',
            'fndant_agbc'    => '"Antecipação"',
            'fndant_ccbc'    => '"Antecipação"',
            'fndant_pix'     => '"Antecipação"',
            'fndant_seller'  => '"Antecipação"',
            'ant_auto_srvd'  => '"Antecipação"',
            'ant_auto_prdvo' => '"Antecipação"',
            'ant_auto_prdvd' => '"Antecipação"',
            // Rebate / Royalties / Comissão
            'rebate_emp'    => 'Loja beneficiada',
            'tax_rebate'    => '"Rebate"',
            'tax_royalties' => '"Rebate"',
            'tax_comiss'    => '"Rebate"',
            'royalties_emp' => 'Loja beneficiada',
            'comiss_emp'    => 'Loja beneficiada',
            // Cobranças
            'cobsrv_atv'      => 'Serviço de cobrança',
            'cobsrv_diasatr'  => '"Cobranças"',
            'cobsrv_multa'    => '"Cobranças"',
            'cobsrv_juros'    => '"Cobranças"',
            'tax_cobsrv_adm'  => '"Cobranças"',
            'tax_cobsrv_juss' => '"Cobranças"',
        ];
    }

    public function messages()
    {
        return [
            // 'emp_rzsoc.required' => 'Campo obrigatório.',
            // 'emp_rzsoc.max' => 'O campo deve conter no máximo 60 caracteres.',
            // 'emp_cnpj.required' => 'Campo obrigatório.',
            // 'emp_cnpj.unique' => 'Já existe uma empresa cadastrada com esse CNPJ.',
            // 'emp_cnpj.min' => 'CNPJ Inválido.',
            // 'emp_cnpj.max' => 'CNPJ Inválido.',
            // 'emp_nfant.required' => 'Campo obrigatório.',
            // 'emp_sts.required' => 'Campo obrigatório.',
            // 'vlr_imp.required' => 'Campo obrigatório.',
            // 'dtvenc_imp.required' => 'Campo obrigatório.',
            // 'cond_pgto.required' => 'Campo obrigatório.',
            // 'vlr_mens.required' => 'Campo obrigatório.',
            // 'dtvenc_mens.required' => 'Campo obrigatório.',
            // 'emp_tpbolet.required' => 'Campo obrigatório.',
            // 'emp_adqrnt.required' => 'Campo obrigatório.',
            // 'emp_meiocom.required' => 'Campo obrigatório.',
            // Endereço
            // 'emp_cep.required' => 'Campo obrigatório.',
            // 'emp_cep.min' => 'Campo inválido.',
            // 'emp_cep.max' => 'Campo inválido.',
            // 'emp_end.max' => 'Campo inválido.',
            // 'emp_end.required' => 'Campo obrigatório.',
            // 'emp_endbair.required' => 'Campo obrigatório.',
            // 'emp_endpais.required' => 'Campo obrigatório.',
            // 'emp_endest.required' => 'Campo obrigatório.',
            // 'emp_endcid.required' => 'Campo obrigatório.',
            // Contatos
            // 'emp_resplg.required' => 'Campo obrigatório.',
            // 'emp_emailrp.required' => 'Campo obrigatório.',
            // 'emp_emailrp.email' => 'E-mail inválido.',
            // 'emp_celrp.required' => 'Campo obrigatório.',
            // 'emp_celrp.min' => 'Celular inválido.',
            // 'emp_celrp.max' => 'Celular inválido.',
            // 'emp_respcm.required' => 'Campo obrigatório.',
            // 'emp_emailcm.required' => 'Campo obrigatório.',
            // 'emp_emailcm.email' => 'E-mail inválido.',
            // 'emp_celcm.required' => 'Campo obrigatório.',
            // 'emp_celcm.min' => 'Celular inválido.',
            // 'emp_celcm.max' => 'Celular inválido.',
            // 'emp_respfi.required' => 'Campo obrigatório.',
            // 'emp_emailfi.required' => 'Campo obrigatório.',
            // 'emp_emailfi.email' => 'E-mail inválido.',
            // 'emp_celfi.required' => 'Campo obrigatório.',
            // 'emp_celfi.min' => 'Celular inválido.',
            // 'emp_celfi.max' => 'Celular inválido.',
            // 'emp_pagweb.required' => 'Campo obrigatório.',
            // 'emp_rdsoc.required' => 'Campo obrigatório.',
            // multban
            // 'emp_cdgbc.required' => 'O campo :attribute da é obrigatório.',
            // 'emp_agbc.required' => ' "multban" Campo obrigatório .',
            // 'emp_ccbc.required' => ' "multban" Campo obrigatório.',
            // 'emp_pix.required' => ' "multban" Campo obrigatório.',
            // 'emp_seller.required' => 'Campo obrigatório.',
            // 'emp_cdgbcs.required' => 'Campo obrigatório.',
            // 'emp_agbcs.required' => 'Campo obrigatório.',
            // 'emp_ccbcs.required' => 'Campo obrigatório.',
            // 'emp_pixs.required' => 'Campo obrigatório.',
            // 'emp_sellers.required' => 'Campo obrigatório.',
            // 'lib_cnscore.required' => 'Campo obrigatório.',
            // 'vlr_pix.required' => 'Campo obrigatório.',
            // 'vlr_boleto.required' => 'Campo obrigatório.',
            // 'vlr_bolepix.required' => 'Campo obrigatório.',
            // 'dias_inat_card.required' => 'Campo obrigatório.',
            // 'isnt_pixblt.required' => 'Campo obrigatório.',
            // 'card_posparc.required' => 'Campo obrigatório.',
            // 'perc_mlt_atr.required' => 'Campo obrigatório.',
            // 'perc_jrs_atr.required' => 'Campo obrigatório.',
            // 'perc_com_mltjr.required' => 'Campo obrigatório.',
            // 'parc_jr_deprc.required' => 'Campo obrigatório.',
            // 'tax_jrsparc.required' => 'Campo obrigatório.',
            // 'parc_com_jrs.required' => 'Campo obrigatório.',
            // 'tax_pre.required' => 'Campo obrigatório.',
            // 'tax_gift.required' => 'Campo obrigatório.',
            // 'tax_fid.required' => 'Campo obrigatório.',
            // Antecipação
            // 'tax_antmult.required_with' => 'Campo obrigatório.',
            // 'tax_antfundo.required_with' => 'Campo obrigatório.',
            // 'perc_rec_ant.required_with' => 'Campo obrigatório.',
            // 'ant_auto_srvd.required' =>  'Campo obrigatório.',
            // 'ant_auto_prdvo.required' => 'Campo obrigatório.',
            // 'ant_auto_prdvd.required' => 'Campo obrigatório.',
            // 'fndant_agbc.required_with' => 'Campo obrigatório.',
            // 'fndant_ccbc.required_with' => 'Campo obrigatório.',
            // 'fndant_pix.required_with' => 'Campo obrigatório.',
            // 'fndant_seller.required_with' => 'Campo obrigatório.',

            // Rebate / Royalties / Comissão
            // 'tax_rebate.required' => 'Campo obrigatório.',
            // 'tax_royalties.required' => 'Campo obrigatório.',
            // 'tax_comiss.required' => 'Campo obrigatório.',
            // //Cobranças
            // 'cobsrv_diasatr.required' => 'Campo obrigatório.',
            // 'cobsrv_multa.required' => 'Campo obrigatório.',
            // 'cobsrv_juros.required' => 'Campo obrigatório.',
            // 'tax_cobsrv_adm.required' => 'Campo obrigatório.',
            // 'tax_cobsrv_juss.required' => 'Campo obrigatório.',
        ];
    }
}
