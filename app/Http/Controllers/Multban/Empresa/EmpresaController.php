<?php

namespace App\Http\Controllers\Multban\Empresa;

use App\Enums\EmpresaStatusEnum;
use App\Enums\FiltrosEnum;
use App\Http\Controllers\Controller;
use App\Models\Multban\Auditoria\LogAuditoria;
use App\Models\Multban\DadosMestre\TbDmBncCode;
use App\Models\Multban\Empresa\DestinoDosValores;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Empresa\EmpresaMeioComomunicacao;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Empresa\EmpresaRamoDeAtividade;
use App\Models\Multban\Empresa\EmpresaStatus;
use App\Models\Multban\Empresa\EmpresaTaxpos;
use App\Models\Multban\Empresa\EmpresaTipoDeAdquirentes;
use App\Models\Multban\Empresa\EmpresaTipoDeBoletagem;
use App\Models\Multban\Empresa\EmpresaTiposDePlanoVendido;
use App\Models\Multban\Endereco\Cidade;
use App\Models\Multban\Endereco\Estados;
use App\Models\Multban\Endereco\Pais;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class EmpresaController extends Controller
{
    private $permissions;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filtros = [
            FiltrosEnum::COD_FRANQUEADORA => 'Código da Franqueadora',
            FiltrosEnum::EMPRESA          => 'Empresa',
            FiltrosEnum::NOME_FANTASIA    => 'Nome Fantasia',
            FiltrosEnum::NOME_MULTBAN     => 'Nome MultBan',
            FiltrosEnum::CNPJ             => 'CNPJ',
        ];
        $status = EmpresaStatus::all();
        $empresaGeral = new Empresa;

        return response(view('Multban.empresa.index', compact('filtros', 'status', 'empresaGeral')));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $empresaGeral = new Empresa;

        $empresaParam = new EmpresaParam;

        $pais = Pais::all();

        $status = EmpresaStatus::all();

        $ratvs = EmpresaRamoDeAtividade::all();

        $tipoDeBoletagem = EmpresaTipoDeBoletagem::all();

        $tiposDePlanoVendido = EmpresaTiposDePlanoVendido::all();

        $tipoDeAdquirentes = EmpresaTipoDeAdquirentes::all();

        $meioComomunicacao = EmpresaMeioComomunicacao::all();

        $codigoDosbancos = TbDmBncCode::all();

        $empresaTaxpos = new Collection;

        $franqueadorMaster = [];

        $rebateLoja = new Empresa;
        $royaltiesLoja = new Empresa;
        $comissaoLoja = new Empresa;

        $destinoDosValores = DestinoDosValores::all();

        $empresaGeral->dtvenc_imp = Carbon::now()->format('d/m/Y');
        $empresaGeral->dtvenc_mens = Carbon::now()->format('d/m/Y');

        return response(
            view(
                'Multban.empresa.edit',
                compact(
                    'empresaGeral',
                    'empresaParam',
                    'pais',
                    'status',
                    'ratvs',
                    'tipoDeBoletagem',
                    'tiposDePlanoVendido',
                    'tipoDeAdquirentes',
                    'meioComomunicacao',
                    'codigoDosbancos',
                    'franqueadorMaster',
                    'empresaTaxpos',
                    'rebateLoja',
                    'royaltiesLoja',
                    'comissaoLoja',
                    'destinoDosValores'
                )
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $empresaGeral = new Empresa;
            $input = $request->all();

            $validaTaxas = $this->validaTaxas($request);

            $hasError = $validaTaxas['hasError'];
            $error_list = $validaTaxas['error_list'];

            if ($hasError) {
                return response()->json([
                    'message' => $error_list['message'],

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $input['blt_ctr'] = $request->blt_ctr == 'on' ? 'x' : '';
            $input['tax_blt'] = formatarTextoParaDecimal($request->tax_blt);
            $input['emp_cnpj'] = removerCNPJ($request->emp_cnpj);
            $input['emp_cep'] = removerMascaraCEP($request->emp_cep);
            $input['emp_ie'] = removerCNPJ($request->emp_ie);
            $input['emp_celrp'] = removerMascaraTelefone($request->emp_celrp);
            $input['emp_celcm'] = removerMascaraTelefone($request->emp_celcm);
            $input['emp_celfi'] = removerMascaraTelefone($request->emp_celfi);
            $input['vlr_imp'] = formatarTextoParaDecimal($request->vlr_imp);
            $input['vlr_mens'] = formatarTextoParaDecimal($request->vlr_mens);
            $input['vlr_pix'] = formatarTextoParaDecimal($request->vlr_pix);
            $input['vlr_boleto'] = formatarTextoParaDecimal($request->vlr_boleto);
            $input['vlr_bolepix'] = formatarTextoParaDecimal($request->vlr_bolepix);
            $input['isnt_pixblt'] = formatarTextoParaDecimal($request->isnt_pixblt);
            $input['tax_antmult'] = formatarTextoParaDecimal($request->tax_antmult);
            $input['tax_antfundo'] = formatarTextoParaDecimal($request->tax_antfundo);
            $input['perc_rec_ant'] = formatarTextoParaDecimal($request->perc_rec_ant);
            $input['dias_inat_card'] = formatarTextoParaDecimal($request->dias_inat_card);
            $input['perc_mlt_atr'] = formatarTextoParaDecimal($request->perc_mlt_atr);
            $input['perc_jrs_atr'] = formatarTextoParaDecimal($request->perc_jrs_atr);
            $input['perc_com_mltjr'] = formatarTextoParaDecimal($request->perc_com_mltjr);
            $input['parc_com_jrs'] = formatarTextoParaDecimal($request->parc_com_jrs);
            $input['tax_pre'] = formatarTextoParaDecimal($request->tax_pre);
            $input['tax_gift'] = formatarTextoParaDecimal($request->tax_gift);
            $input['tax_fid'] = formatarTextoParaDecimal($request->tax_fid);
            $input['card_posparc'] = formatarTextoParaDecimal($request->card_posparc);
            $input['blt_parclib'] = formatarTextoParaDecimal($request->blt_parclib);
            $input['tax_comiss'] = formatarTextoParaDecimal($request->tax_comiss);
            $input['tax_royalties'] = formatarTextoParaDecimal($request->tax_royalties);
            $input['tax_rebate'] = formatarTextoParaDecimal($request->tax_rebate);
            $input['emp_integra'] = $request->emp_integra == 'on' ? 'x' : '';
            $input['emp_checkb'] = $request->emp_checkb == 'on' ? 'x' : '';
            $input['emp_checkm'] = $request->emp_checkm == 'on' ? 'x' : '';
            $input['emp_checkc'] = $request->emp_checkc == 'on' ? 'x' : '';
            $input['emp_reemb'] = $request->emp_reemb == 'on' ? 'x' : '';
            $input['lib_cnscore'] = $request->lib_cnscore == 'on' ? 'x' : '';
            $input['card_posctr'] = $request->card_posctr == 'on' ? 'x' : '';
            $input['cob_mltjr_atr'] = $request->cob_mltjr_atr == 'on' ? 'x' : '';
            $input['parc_cjuros'] = $request->parc_cjuros == 'on' ? 'x' : '';
            $input['card_prectr'] = $request->card_prectr == 'on' ? 'x' : '';
            $input['card_giftctr'] = $request->card_giftctr == 'on' ? 'x' : '';
            $input['card_fidctr'] = $request->card_fidctr == 'on' ? 'x' : '';
            $input['antecip_ctr'] = $request->antecip_ctr == 'on' ? 'x' : '';
            $input['antecip_auto'] = $request->antecip_auto == 'on' ? 'x' : '';
            $input['ant_blktit'] = $request->ant_blktit == 'on' ? 'x' : '';
            $input['ant_titpdv'] = $request->ant_titpdv == 'on' ? 'x' : '';
            $input['emp_wl'] = $request->emp_wl == 'on' ? 'x' : '';
            $input['emp_privlbl'] = $request->emp_privlbl == 'on' ? 'x' : '';
            $input['pp_particular'] = $request->pp_particular == 'on' ? 'x' : '';
            $input['pp_franquia'] = $request->pp_franquia == 'on' ? 'x' : '';
            $input['pp_mult'] = $request->pp_mult == 'on' ? 'x' : '';
            $input['pp_cashback'] = $request->pp_cashback == 'on' ? 'x' : '';
            $input['cobsrv_multa'] = formatarTextoParaDecimal($request->cobsrv_multa);
            $input['cobsrv_juros'] = formatarTextoParaDecimal($request->cobsrv_juros);
            $input['tax_cobsrv_adm'] = formatarTextoParaDecimal($request->tax_cobsrv_adm);
            $input['tax_cobsrv_juss'] = formatarTextoParaDecimal($request->tax_cobsrv_juss);

            $validator = Validator::make($input, $empresaGeral->rules(), $empresaGeral->messages(), $empresaGeral->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresaParam = new EmpresaParam;

            if ($request->pagar_por == 'rebate_split') {

                $empresaParam->rebate_split = 'x';
                $empresaParam->rebate_transf = '';
            }

            if ($request->pagar_por == 'rebate_transf') {

                $empresaParam->rebate_transf = 'x';
                $empresaParam->rebate_split = '';
            }

            if ($request->royalties_paghar_por == 'royalties_split') {

                $empresaParam->royalties_split = 'x';
                $empresaParam->royalties_transf = '';
            }

            if ($request->royalties_paghar_por == 'royalties_transf') {

                $empresaParam->royalties_transf = 'x';
                $empresaParam->royalties_split = '';
            }

            if ($request->comissao_paghar_por == 'comiss_split') {

                $empresaParam->comiss_split = 'x';
                $empresaParam->comiss_transf = '';
            }

            if ($request->comissao_paghar_por == 'comiss_transf') {

                $empresaParam->comiss_split = '';
                $empresaParam->comiss_transf = 'x';
            }

            if ($request->inadimplencia == 'inad_descprox') {

                $empresaParam->inad_descprox = 'x';
                $empresaParam->inad_semrisco = '';
            }

            if ($request->inadimplencia == 'inad_semrisco') {

                $empresaParam->inad_descprox = '';
                $empresaParam->inad_semrisco = 'x';
            }

            $empresaParam->blt_ctr = $request->blt_ctr == 'on' ? 'x' : '';
            $empresaParam->tax_blt = $input['tax_blt'];
            $empresaParam->emp_cdgbc = $request->emp_cdgbc;
            $empresaParam->emp_agbc = $request->emp_agbc;
            $empresaParam->emp_ccbc = $request->emp_ccbc;
            $empresaParam->emp_pix = $request->emp_pix;
            $empresaParam->emp_seller = $request->emp_seller;
            $empresaParam->emp_cdgbcs = $request->emp_cdgbcs;
            $empresaParam->emp_agbcs = $request->emp_agbcs;
            $empresaParam->emp_ccbcs = $request->emp_ccbcs;
            $empresaParam->emp_pixs = $request->emp_pixs;
            $empresaParam->emp_sellers = $request->emp_sellers;
            $empresaParam->vlr_pix = formatarTextoParaDecimal($request->vlr_pix);
            $empresaParam->vlr_boleto = formatarTextoParaDecimal($request->vlr_boleto);
            $empresaParam->vlr_bolepix = formatarTextoParaDecimal($request->vlr_bolepix);
            $empresaParam->dias_inat_card = $request->dias_inat_card;
            $empresaParam->isnt_pixblt = formatarTextoParaDecimal($request->isnt_pixblt);
            $empresaParam->lib_cnscore = $request->lib_cnscore == 'on' ? 'x' : '';
            $empresaParam->intervalo_mes = $request->intervalo_mes;
            $empresaParam->qtde_cns_freem = $request->qtde_cns_freem;
            $empresaParam->qtde_cns_cntrm = $request->qtde_cns_cntrm;
            $empresaParam->card_posctr = $request->card_posctr == 'on' ? 'x' : '';
            $empresaParam->card_posparc = formatarTextoParaDecimal($request->card_posparc);
            $empresaParam->blt_parclib = formatarTextoParaDecimal($request->blt_parclib);
            $empresaParam->cob_mltjr_atr = $request->cob_mltjr_atr == 'on' ? 'x' : '';
            $empresaParam->perc_mlt_atr = formatarTextoParaDecimal($request->perc_mlt_atr);
            $empresaParam->perc_jrs_atr = formatarTextoParaDecimal($request->perc_jrs_atr);
            $empresaParam->perc_com_mltjr = formatarTextoParaDecimal($request->perc_com_mltjr);
            $empresaParam->parc_cjuros = $request->parc_cjuros == 'on' ? 'x' : '';
            $empresaParam->parc_jr_deprc = $request->parc_jr_deprc;
            $empresaParam->tax_jrsparc = formatarTextoParaDecimal($request->tax_jrsparc);
            $empresaParam->parc_com_jrs = formatarTextoParaDecimal($request->parc_com_jrs);
            $empresaParam->card_prectr = $request->card_prectr == 'on' ? 'x' : '';
            $empresaParam->tax_pre = formatarTextoParaDecimal($request->tax_pre);
            $empresaParam->card_giftctr = $request->card_giftctr == 'on' ? 'x' : '';
            $empresaParam->tax_gift = formatarTextoParaDecimal($request->tax_gift);
            $empresaParam->card_fidctr = $request->card_fidctr == 'on' ? 'x' : '';
            $empresaParam->tax_fid = formatarTextoParaDecimal($request->tax_fid);
            $empresaParam->antecip_ctr = $request->antecip_ctr == 'on' ? 'x' : '';
            $empresaParam->tax_antmult = formatarTextoParaDecimal($request->tax_antmult);
            $empresaParam->tax_antfundo = formatarTextoParaDecimal($request->tax_antfundo);
            $empresaParam->perc_rec_ant = formatarTextoParaDecimal($request->perc_rec_ant);
            $empresaParam->fndant_cdgbc = $request->fndant_cdgbc;
            $empresaParam->fndant_agbc = $request->fndant_agbc;
            $empresaParam->fndant_ccbc = $request->fndant_ccbc;
            $empresaParam->fndant_pix = $request->fndant_pix;
            $empresaParam->fndant_seller = $request->fndant_seller;
            $empresaParam->antecip_auto = $request->antecip_auto == 'on' ? 'x' : '';
            $empresaParam->ant_auto_srvd = $request->ant_auto_srvd;
            $empresaParam->ant_auto_prdvo = $request->ant_auto_prdvo;
            $empresaParam->ant_auto_prdvd = $request->ant_auto_prdvd;
            $empresaParam->rebate_emp = $request->rebate_emp;
            $empresaParam->royalties_emp = $request->royalties_emp;
            $empresaParam->comiss_emp = $request->comiss_emp;

            $empresaParam->tax_comiss = formatarTextoParaDecimal($request->tax_comiss);
            $empresaParam->tax_royalties = formatarTextoParaDecimal($request->tax_royalties);
            $empresaParam->tax_rebate = formatarTextoParaDecimal($request->tax_rebate);

            $empresaParam->cobsrv_atv = $request->cobsrv_atv == 'on' ? 'x' : '';
            $empresaParam->cobsrv_diasatr = $request->cobsrv_diasatr;
            $empresaParam->cobsrv_multa = $input['cobsrv_multa'];
            $empresaParam->cobsrv_juros = $input['cobsrv_juros'];
            $empresaParam->tax_cobsrv_adm = $input['tax_cobsrv_adm'];
            $empresaParam->tax_cobsrv_juss = $input['tax_cobsrv_juss'];

            $empresaParam->pp_particular = $input['pp_particular'];
            $empresaParam->pp_franquia = $input['pp_franquia'];
            $empresaParam->pp_mult = $input['pp_mult'];
            $empresaParam->pp_cashback = $input['pp_cashback'];
            $empresaParam->ant_blktit = $input['ant_blktit'];
            $empresaParam->ant_titpdv = $input['ant_titpdv'];
            $empresaParam->emp_destvlr = $input['emp_destvlr'];
            $empresaParam->emp_dbaut = $request->emp_dbaut == 'on' ? 'x' : '';

            $empresaParam->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
            $empresaParam->dthr_cr = Carbon::now();
            $empresaParam->dthr_ch = Carbon::now();

            $empresaGeral->emp_cnpj = removerCNPJ($request->emp_cnpj);
            $empresaGeral->emp_wl = $request->emp_wl == 'on' ? 'x' : '';
            $empresaGeral->emp_comwl = formatarTextoParaDecimal($request->emp_comwl);
            $empresaGeral->emp_privlbl = $request->emp_privlbl == 'on' ? 'x' : '';
            $empresaGeral->emp_sts = $request->emp_sts;
            $empresaGeral->emp_ie = removerCNPJ($request->emp_ie);
            $empresaGeral->emp_im = removerCNPJ($request->emp_im);
            $empresaGeral->emp_rzsoc = mb_strtoupper(rtrim($request->emp_rzsoc), 'UTF-8');
            $empresaGeral->emp_nfant = mb_strtoupper(rtrim($request->emp_nfant), 'UTF-8');
            $empresaGeral->emp_nmult = $request->emp_nmult;
            $empresaGeral->emp_ratv = $request->emp_ratv;

            if ($request->emp_frq == 'sim') {
                $empresaGeral->emp_frqmst = $request->emp_frqmst;
                $empresaGeral->emp_frq = 'x';
            } else {
                $empresaGeral->emp_frqmst = null;
                $empresaGeral->emp_frq = '';
            }

            if ($request->emp_frqcmp == 'sim') {
                $empresaGeral->emp_frqcmp = 'x';
            } else {
                $empresaGeral->emp_frqcmp = '';
            }

            if ($request->emp_altlmt == 'sim') {
                $empresaGeral->emp_altlmt = 'x';
            } else {
                $empresaGeral->emp_altlmt = '';
            }

            $empresaGeral->emp_tpbolet = $request->emp_tpbolet;
            $empresaGeral->tp_plano = $request->tp_plano;
            $empresaGeral->emp_adqrnt = $request->emp_adqrnt;
            $empresaGeral->emp_meiocom = $request->emp_meiocom;
            $empresaGeral->vlr_imp = formatarTextoParaDecimal($request->vlr_imp);
            $empresaGeral->dtvenc_imp = Carbon::createFromFormat('d/m/Y', $request->dtvenc_imp)->format('Y-m-d');
            $empresaGeral->cond_pgto = $request->cond_pgto;
            $empresaGeral->vlr_mens = formatarTextoParaDecimal($request->vlr_mens);
            $empresaGeral->dtvenc_mens = Carbon::createFromFormat('d/m/Y', $request->dtvenc_mens)->format('Y-m-d');
            $empresaGeral->emp_cep = removerMascaraCEP($request->emp_cep);
            $empresaGeral->emp_end = $request->emp_end;
            $empresaGeral->emp_endnum = $request->emp_endnum;
            $empresaGeral->emp_endcmp = $request->emp_endcmp;
            $empresaGeral->emp_endbair = $request->emp_endbair;
            $empresaGeral->emp_endpais = $request->emp_endpais;
            $empresaGeral->emp_endest = $request->emp_endest;
            $empresaGeral->emp_endcid = $request->emp_endcid;
            $empresaGeral->emp_resplg = $request->emp_resplg;
            $empresaGeral->emp_emailrp = $request->emp_emailrp;
            $empresaGeral->emp_celrp = removerMascaraTelefone($request->emp_celrp);
            $empresaGeral->emp_respcm = $request->emp_respcm;
            $empresaGeral->emp_emailcm = $request->emp_emailcm;
            $empresaGeral->emp_celcm = removerMascaraTelefone($request->emp_celcm);
            $empresaGeral->emp_respfi = $request->emp_respfi;
            $empresaGeral->emp_emailfi = $request->emp_emailfi;
            $empresaGeral->emp_celfi = removerMascaraTelefone($request->emp_celfi);
            $empresaGeral->emp_pagweb = $request->emp_pagweb;
            $empresaGeral->emp_rdsoc = $request->emp_rdsoc;

            $empresaGeral->emp_integra = $input['emp_integra'];
            $empresaGeral->emp_checkb = $input['emp_checkb'];
            $empresaGeral->emp_checkm = $input['emp_checkm'];
            $empresaGeral->emp_checkc = $input['emp_checkc'];
            $empresaGeral->emp_reemb = $input['emp_reemb'];

            if ($request->hasFile('logoFile')) {
                $empresaId = $empresaGeral->emp_id;
                // Remove o arquivo anterior, se existir
                if ($empresaGeral->logo_path && Storage::disk('public')->exists($empresaGeral->logo_path)) {
                    Storage::disk('public')->delete($empresaGeral->logo_path);
                }
                $file = $request->file('logoFile');
                $extension = $file->getClientOriginalExtension();
                $filename = "logo.$extension";
                $path = $file->storeAs("logos/empresa_$empresaId", $filename, 'public');
                $empresaGeral->logo_path = $path;
            }

            $empresaGeral->dthr_cr = Carbon::now();
            $empresaGeral->modificador = \Illuminate\Support\Facades\Auth::user()->user_id;
            $empresaGeral->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
            $empresaGeral->dthr_ch = Carbon::now();

            $empresaGeral->save();

            $empresaParam->emp_id = $empresaGeral->emp_id;

            $empresaParam->save();

            $this->gravaTaxas($request, $empresaGeral->emp_id);

            $logAuditoria = new LogAuditoria;
            $logAuditoria->auddat = date('Y-m-d H:i:s');
            $logAuditoria->audusu = \Illuminate\Support\Facades\Auth::user()->user_name;
            $logAuditoria->audtar = 'Adicionou a empresa ';
            $logAuditoria->audarq = $empresaParam->getTable();
            $logAuditoria->audlan = $empresaParam->emp_id;
            $logAuditoria->audant = '';
            $logAuditoria->auddep = '';
            $logAuditoria->audnip = request()->ip();

            $logAuditoria->save();

            DB::commit();

            Session::flash('success', 'Empresa cadastrada com sucesso.');

            Session::flash('idModeloInserido', $empresaGeral->emp_id);

            return response()->json([
                'message' => 'Processando...',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $empresaGeral = Empresa::find($id);

        $franqueadorMaster = Empresa::where('emp_frq', 'x')->get();

        $pais = Pais::all();

        $status = EmpresaStatus::all();

        $ratvs = EmpresaRamoDeAtividade::all();

        $tipoDeBoletagem = EmpresaTipoDeBoletagem::all();

        $tiposDePlanoVendido = EmpresaTiposDePlanoVendido::all();

        $tipoDeAdquirentes = EmpresaTipoDeAdquirentes::all();

        $meioComomunicacao = EmpresaMeioComomunicacao::all();

        $codigoDosbancos = TbDmBncCode::all();

        $empresaTaxpos = EmpresaTaxpos::where('emp_id', $id)->get();

        if (! $empresaGeral) {
            return response(redirect('/empresa')->with('error', 'Opps, empresa não encontrada.'));
        }

        $empresaParam = EmpresaParam::find($empresaGeral->emp_id);

        $empresaGeral->dtvenc_imp = Carbon::createFromFormat('Y-m-d', $empresaGeral->dtvenc_imp)->format('d/m/Y');
        $empresaGeral->dtvenc_mens = Carbon::createFromFormat('Y-m-d', $empresaGeral->dtvenc_mens)->format('d/m/Y');

        return response(
            view(
                'Multban.empresa.edit',
                compact(
                    'empresaGeral',
                    'empresaParam',
                    'pais',
                    'status',
                    'ratvs',
                    'tipoDeBoletagem',
                    'tiposDePlanoVendido',
                    'tipoDeAdquirentes',
                    'meioComomunicacao',
                    'codigoDosbancos',
                    'franqueadorMaster',
                    'empresaTaxpos'
                )
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $empresaGeral = Empresa::find($id);

        $franqueadorMaster = Empresa::where('emp_frq', 'x')->get();

        $pais = Pais::all();

        $status = EmpresaStatus::all();

        $ratvs = EmpresaRamoDeAtividade::all();

        $tipoDeBoletagem = EmpresaTipoDeBoletagem::all();

        $tiposDePlanoVendido = EmpresaTiposDePlanoVendido::all();

        $tipoDeAdquirentes = EmpresaTipoDeAdquirentes::all();

        $meioComomunicacao = EmpresaMeioComomunicacao::all();

        $codigoDosbancos = TbDmBncCode::all();

        $empresaTaxpos = EmpresaTaxpos::where('emp_id', $id)->get();

        if (! $empresaGeral) {
            return response(redirect('/empresa')->with('error', 'Opps, empresa não encontrada.'));
        }

        $empresaParam = EmpresaParam::find($empresaGeral->emp_id);

        $rebateLoja = new Empresa;
        $royaltiesLoja = new Empresa;
        $comissaoLoja = new Empresa;

        if ($empresaParam) {
            $rebateLoja = Empresa::find($empresaParam->rebate_emp);
            $royaltiesLoja = Empresa::find($empresaParam->royalties_emp);
            $comissaoLoja = Empresa::find($empresaParam->comiss_emp);
        }

        $destinoDosValores = DestinoDosValores::all();

        $empresaGeral->dtvenc_imp = $empresaGeral->dtvenc_imp ? Carbon::createFromFormat('Y-m-d', $empresaGeral->dtvenc_imp)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $empresaGeral->dtvenc_mens = $empresaGeral->dtvenc_mens ? Carbon::createFromFormat('Y-m-d', $empresaGeral->dtvenc_mens)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $empresaParam->tax_blt = number_format($empresaParam->tax_blt, 2, ',', '');
        $empresaParam->perc_mlt_atr = number_format($empresaParam->perc_mlt_atr, 2, ',', '');
        $empresaParam->perc_jrs_atr = number_format($empresaParam->perc_jrs_atr, 2, ',', '');
        $empresaParam->perc_com_mltjr = number_format($empresaParam->perc_com_mltjr, 2, ',', '');
        $empresaParam->tax_jrsparc = number_format($empresaParam->tax_jrsparc, 2, ',', '');
        $empresaParam->parc_com_jrs = number_format($empresaParam->parc_com_jrs, 2, ',', '');
        $empresaParam->tax_antmult = number_format($empresaParam->tax_antmult, 2, ',', '');
        $empresaParam->tax_antfundo = number_format($empresaParam->tax_antfundo, 2, ',', '');
        $empresaParam->perc_rec_ant = number_format($empresaParam->perc_rec_ant, 2, ',', '');
        $empresaParam->cobsrv_multa = number_format($empresaParam->cobsrv_multa, 2, ',', '');
        $empresaParam->cobsrv_juros = number_format($empresaParam->cobsrv_juros, 2, ',', '');
        $empresaParam->tax_cobsrv_adm = number_format($empresaParam->tax_cobsrv_adm, 2, ',', '');
        $empresaParam->tax_cobsrv_juss = number_format($empresaParam->tax_cobsrv_juss, 2, ',', '');
        $empresaParam->tax_rebate = number_format($empresaParam->tax_rebate, 2, ',', '');
        $empresaParam->tax_royalties = number_format($empresaParam->tax_royalties, 2, ',', '');
        $empresaParam->tax_comiss = number_format($empresaParam->tax_comiss, 2, ',', '');

        return response(
            view(
                'Multban.empresa.edit',
                compact(
                    'rebateLoja',
                    'royaltiesLoja',
                    'comissaoLoja',
                    'empresaGeral',
                    'empresaParam',
                    'pais',
                    'status',
                    'ratvs',
                    'tipoDeBoletagem',
                    'tiposDePlanoVendido',
                    'tipoDeAdquirentes',
                    'meioComomunicacao',
                    'codigoDosbancos',
                    'franqueadorMaster',
                    'empresaTaxpos',
                    'destinoDosValores'
                )
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $empresaGeral = Empresa::find($id);
            $input = $request->all();

            $validaTaxas = $this->validaTaxas($request);

            $hasError = $validaTaxas['hasError'];
            $error_list = $validaTaxas['error_list'];

            if ($hasError) {
                return response()->json([
                    'message' => $error_list['message'],

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $input['blt_ctr'] = $request->blt_ctr == 'on' ? 'x' : '';
            $input['tax_blt'] = formatarTextoParaDecimal($request->tax_blt);
            $input['emp_cnpj'] = removerCNPJ($request->emp_cnpj);
            $input['emp_cep'] = removerMascaraCEP($request->emp_cep);
            $input['emp_ie'] = removerCNPJ($request->emp_ie);
            $input['emp_celrp'] = removerMascaraTelefone($request->emp_celrp);
            $input['emp_celcm'] = removerMascaraTelefone($request->emp_celcm);
            $input['emp_celfi'] = removerMascaraTelefone($request->emp_celfi);
            $input['vlr_imp'] = formatarTextoParaDecimal($request->vlr_imp);
            $input['vlr_mens'] = formatarTextoParaDecimal($request->vlr_mens);
            $input['vlr_pix'] = formatarTextoParaDecimal($request->vlr_pix);
            $input['vlr_boleto'] = formatarTextoParaDecimal($request->vlr_boleto);
            $input['vlr_bolepix'] = formatarTextoParaDecimal($request->vlr_bolepix);
            $input['isnt_pixblt'] = formatarTextoParaDecimal($request->isnt_pixblt);
            $input['tax_antmult'] = formatarTextoParaDecimal($request->tax_antmult);
            $input['tax_antfundo'] = formatarTextoParaDecimal($request->tax_antfundo);
            $input['perc_rec_ant'] = formatarTextoParaDecimal($request->perc_rec_ant);
            $input['dias_inat_card'] = formatarTextoParaDecimal($request->dias_inat_card);
            $input['perc_mlt_atr'] = formatarTextoParaDecimal($request->perc_mlt_atr);
            $input['perc_jrs_atr'] = formatarTextoParaDecimal($request->perc_jrs_atr);
            $input['perc_com_mltjr'] = formatarTextoParaDecimal($request->perc_com_mltjr);
            $input['parc_com_jrs'] = formatarTextoParaDecimal($request->parc_com_jrs);
            $input['tax_pre'] = formatarTextoParaDecimal($request->tax_pre);
            $input['tax_gift'] = formatarTextoParaDecimal($request->tax_gift);
            $input['tax_fid'] = formatarTextoParaDecimal($request->tax_fid);
            $input['card_posparc'] = formatarTextoParaDecimal($request->card_posparc);
            $input['blt_parclib'] = formatarTextoParaDecimal($request->blt_parclib);
            $input['tax_comiss'] = formatarTextoParaDecimal($request->tax_comiss);
            $input['tax_royalties'] = formatarTextoParaDecimal($request->tax_royalties);
            $input['tax_rebate'] = formatarTextoParaDecimal($request->tax_rebate);
            $input['parc_jr_deprc'] = formatarTextoParaDecimal($request->parc_jr_deprc);
            $input['tax_jrsparc'] = formatarTextoParaDecimal($request->tax_jrsparc);
            $input['emp_integra'] = $request->emp_integra == 'on' ? 'x' : '';
            $input['emp_checkb'] = $request->emp_checkb == 'on' ? 'x' : '';
            $input['emp_checkm'] = $request->emp_checkm == 'on' ? 'x' : '';
            $input['emp_checkc'] = $request->emp_checkc == 'on' ? 'x' : '';
            $input['emp_reemb'] = $request->emp_reemb == 'on' ? 'x' : '';
            $input['lib_cnscore'] = $request->lib_cnscore == 'on' ? 'x' : '';
            $input['card_posctr'] = $request->card_posctr == 'on' ? 'x' : '';
            $input['cob_mltjr_atr'] = $request->cob_mltjr_atr == 'on' ? 'x' : '';
            $input['parc_cjuros'] = $request->parc_cjuros == 'on' ? 'x' : '';
            $input['card_prectr'] = $request->card_prectr == 'on' ? 'x' : '';
            $input['card_giftctr'] = $request->card_giftctr == 'on' ? 'x' : '';
            $input['card_fidctr'] = $request->card_fidctr == 'on' ? 'x' : '';
            $input['antecip_ctr'] = $request->antecip_ctr == 'on' ? 'x' : '';
            $input['antecip_auto'] = $request->antecip_auto == 'on' ? 'x' : '';
            $input['ant_blktit'] = $request->ant_blktit == 'on' ? 'x' : '';
            $input['ant_titpdv'] = $request->ant_titpdv == 'on' ? 'x' : '';
            $input['emp_wl'] = $request->emp_wl == 'on' ? 'x' : '';
            $input['emp_privlbl'] = $request->emp_privlbl == 'on' ? 'x' : '';
            $input['pp_particular'] = $request->pp_particular == 'on' ? 'x' : '';
            $input['pp_franquia'] = $request->pp_franquia == 'on' ? 'x' : '';
            $input['pp_mult'] = $request->pp_mult == 'on' ? 'x' : '';
            $input['pp_cashback'] = $request->pp_cashback == 'on' ? 'x' : '';
            $input['cobsrv_multa'] = formatarTextoParaDecimal($request->cobsrv_multa);
            $input['cobsrv_juros'] = formatarTextoParaDecimal($request->cobsrv_juros);
            $input['tax_cobsrv_adm'] = formatarTextoParaDecimal($request->tax_cobsrv_adm);
            $input['tax_cobsrv_juss'] = formatarTextoParaDecimal($request->tax_cobsrv_juss);

            $validator = Validator::make($input, $empresaGeral->rules($id), $empresaGeral->messages(), $empresaGeral->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($empresaGeral) {

                $empresaParam = EmpresaParam::find($empresaGeral->emp_id);

                // Verifica se ouve mudanças nos campos Empresa, se sim grava na auditoria
                foreach ($input as $key => $value) {
                    if (Arr::exists($empresaGeral->toArray(), $key)) {
                        if ($value != $empresaGeral->$key) {
                            $logAuditoria = new LogAuditoria;
                            $logAuditoria->auddat = date('Y-m-d H:i:s');
                            $logAuditoria->audusu = \Illuminate\Support\Facades\Auth::user()->user_name;
                            $logAuditoria->audtar = 'Alterou o campo ' . $key;
                            $logAuditoria->audarq = $empresaGeral->getTable();
                            $logAuditoria->audlan = $empresaGeral->emp_id;
                            $logAuditoria->audant = $empresaGeral->$key;
                            $logAuditoria->auddep = $value;
                            $logAuditoria->audnip = request()->ip();

                            $logAuditoria->save();
                        }
                    }

                    if (Arr::exists($empresaParam->toArray(), $key)) {

                        if ($value != $empresaParam->$key) {
                            $logAuditoria = new LogAuditoria;
                            $logAuditoria->auddat = date('Y-m-d H:i:s');
                            $logAuditoria->audusu = \Illuminate\Support\Facades\Auth::user()->user_name;
                            $logAuditoria->audtar = 'Alterou o campo ' . $key;
                            $logAuditoria->audarq = $empresaParam->getTable();
                            $logAuditoria->audlan = $empresaParam->emp_id;
                            $logAuditoria->audant = $empresaParam->$key;
                            $logAuditoria->auddep = $value;
                            $logAuditoria->audnip = request()->ip();

                            $logAuditoria->save();
                        }
                    }
                }

                if ($request->pagar_por == 'rebate_split') {

                    $empresaParam->rebate_split = 'x';
                    $empresaParam->rebate_transf = '';
                }

                if ($request->pagar_por == 'rebate_transf') {

                    $empresaParam->rebate_transf = 'x';
                    $empresaParam->rebate_split = '';
                }

                if ($request->royalties_paghar_por == 'royalties_split') {

                    $empresaParam->royalties_split = 'x';
                    $empresaParam->royalties_transf = '';
                }

                if ($request->royalties_paghar_por == 'royalties_transf') {

                    $empresaParam->royalties_transf = 'x';
                    $empresaParam->royalties_split = '';
                }

                if ($request->comissao_paghar_por == 'comiss_split') {

                    $empresaParam->comiss_split = 'x';
                    $empresaParam->comiss_transf = '';
                }

                if ($request->comissao_paghar_por == 'comiss_transf') {

                    $empresaParam->comiss_split = '';
                    $empresaParam->comiss_transf = 'x';
                }

                if ($request->inadimplencia == 'inad_descprox') {

                    $empresaParam->inad_descprox = 'x';
                    $empresaParam->inad_semrisco = '';
                }

                if ($request->inadimplencia == 'inad_semrisco') {

                    $empresaParam->inad_descprox = '';
                    $empresaParam->inad_semrisco = 'x';
                }

                $empresaParam->blt_ctr = $request->blt_ctr == 'on' ? 'x' : '';
                $empresaParam->tax_blt = $input['tax_blt'];
                $empresaParam->emp_cdgbc = $request->emp_cdgbc;
                $empresaParam->emp_agbc = $request->emp_agbc;
                $empresaParam->emp_ccbc = $request->emp_ccbc;
                $empresaParam->emp_pix = $request->emp_pix;
                $empresaParam->emp_seller = $request->emp_seller;
                $empresaParam->emp_cdgbcs = $request->emp_cdgbcs;
                $empresaParam->emp_agbcs = $request->emp_agbcs;
                $empresaParam->emp_ccbcs = $request->emp_ccbcs;
                $empresaParam->emp_pixs = $request->emp_pixs;
                $empresaParam->emp_sellers = $request->emp_sellers;
                $empresaParam->vlr_pix = $input['vlr_pix'];
                $empresaParam->vlr_boleto = formatarTextoParaDecimal($request->vlr_boleto);
                $empresaParam->vlr_bolepix = formatarTextoParaDecimal($request->vlr_bolepix);
                $empresaParam->dias_inat_card = $request->dias_inat_card;
                $empresaParam->isnt_pixblt = formatarTextoParaDecimal($request->isnt_pixblt);
                $empresaParam->lib_cnscore = $request->lib_cnscore == 'on' ? 'x' : '';
                $empresaParam->intervalo_mes = $request->intervalo_mes;
                $empresaParam->qtde_cns_freem = $request->qtde_cns_freem;
                $empresaParam->qtde_cns_cntrm = $request->qtde_cns_cntrm;
                $empresaParam->card_posctr = $request->card_posctr == 'on' ? 'x' : '';
                $empresaParam->card_posparc = formatarTextoParaDecimal($request->card_posparc);
                $empresaParam->blt_parclib = formatarTextoParaDecimal($request->blt_parclib);
                $empresaParam->cob_mltjr_atr = $request->cob_mltjr_atr == 'on' ? 'x' : '';
                $empresaParam->perc_mlt_atr = formatarTextoParaDecimal($request->perc_mlt_atr);
                $empresaParam->perc_jrs_atr = formatarTextoParaDecimal($request->perc_jrs_atr);
                $empresaParam->perc_com_mltjr = formatarTextoParaDecimal($request->perc_com_mltjr);
                $empresaParam->parc_cjuros = $request->parc_cjuros == 'on' ? 'x' : '';
                $empresaParam->parc_jr_deprc = $request->parc_jr_deprc;
                $empresaParam->tax_jrsparc = formatarTextoParaDecimal($request->tax_jrsparc);
                $empresaParam->parc_com_jrs = formatarTextoParaDecimal($request->parc_com_jrs);
                $empresaParam->card_prectr = $request->card_prectr == 'on' ? 'x' : '';
                $empresaParam->tax_pre = formatarTextoParaDecimal($request->tax_pre);
                $empresaParam->card_giftctr = $request->card_giftctr == 'on' ? 'x' : '';
                $empresaParam->tax_gift = formatarTextoParaDecimal($request->tax_gift);
                $empresaParam->card_fidctr = $request->card_fidctr == 'on' ? 'x' : '';
                $empresaParam->tax_fid = formatarTextoParaDecimal($request->tax_fid);
                $empresaParam->antecip_ctr = $request->antecip_ctr == 'on' ? 'x' : '';
                $empresaParam->tax_antmult = formatarTextoParaDecimal($request->tax_antmult);
                $empresaParam->tax_antfundo = formatarTextoParaDecimal($request->tax_antfundo);
                $empresaParam->perc_rec_ant = formatarTextoParaDecimal($request->perc_rec_ant);
                $empresaParam->fndant_cdgbc = $request->fndant_cdgbc;
                $empresaParam->fndant_agbc = $request->fndant_agbc;
                $empresaParam->fndant_ccbc = $request->fndant_ccbc;
                $empresaParam->fndant_pix = $request->fndant_pix;
                $empresaParam->fndant_seller = $request->fndant_seller;
                $empresaParam->antecip_auto = $request->antecip_auto == 'on' ? 'x' : '';
                $empresaParam->ant_auto_srvd = $request->ant_auto_srvd;
                $empresaParam->ant_auto_prdvo = $request->ant_auto_prdvo;
                $empresaParam->ant_auto_prdvd = $request->ant_auto_prdvd;
                $empresaParam->rebate_emp = $request->rebate_emp;
                $empresaParam->royalties_emp = $request->royalties_emp;
                $empresaParam->comiss_emp = $request->comiss_emp;

                $empresaParam->tax_comiss = formatarTextoParaDecimal($request->tax_comiss);
                $empresaParam->tax_royalties = formatarTextoParaDecimal($request->tax_royalties);
                $empresaParam->tax_rebate = formatarTextoParaDecimal($request->tax_rebate);

                $empresaParam->cobsrv_atv = $request->cobsrv_atv == 'on' ? 'x' : '';

                $empresaParam->cobsrv_diasatr = $request->cobsrv_diasatr;
                $empresaParam->cobsrv_multa = $input['cobsrv_multa'];
                $empresaParam->cobsrv_juros = $input['cobsrv_juros'];
                $empresaParam->tax_cobsrv_adm = $input['tax_cobsrv_adm'];
                $empresaParam->tax_cobsrv_juss = $input['tax_cobsrv_juss'];

                $empresaParam->pp_particular = $input['pp_particular'];
                $empresaParam->pp_franquia = $input['pp_franquia'];
                $empresaParam->pp_mult = $input['pp_mult'];
                $empresaParam->pp_cashback = $input['pp_cashback'];
                $empresaParam->ant_blktit = $input['ant_blktit'];
                $empresaParam->ant_titpdv = $input['ant_titpdv'];
                $empresaParam->emp_destvlr = $input['emp_destvlr'];
                $empresaParam->emp_dbaut = $request->emp_dbaut == 'on' ? 'x' : '';

                $empresaParam->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $empresaParam->dthr_cr = Carbon::now();
                $empresaParam->dthr_ch = Carbon::now();

                $empresaGeral->emp_cnpj = removerCNPJ($request->emp_cnpj);
                $empresaGeral->emp_wl = $request->emp_wl == 'on' ? 'x' : '';
                $empresaGeral->emp_comwl = formatarTextoParaDecimal($request->emp_comwl);
                $empresaGeral->emp_privlbl = $request->emp_privlbl == 'on' ? 'x' : '';
                $empresaGeral->emp_sts = $request->emp_sts;
                $empresaGeral->emp_ie = removerCNPJ($request->emp_ie);
                $empresaGeral->emp_im = removerCNPJ($request->emp_im);
                $empresaGeral->emp_rzsoc = mb_strtoupper(rtrim($request->emp_rzsoc), 'UTF-8');
                $empresaGeral->emp_nfant = mb_strtoupper(rtrim($request->emp_nfant), 'UTF-8');
                $empresaGeral->emp_nmult = $request->emp_nmult;
                $empresaGeral->emp_ratv = $request->emp_ratv;

                if ($request->emp_frq == 'sim') {
                    $empresaGeral->emp_frqmst = null;
                    $empresaGeral->emp_frq = 'x';
                } else {
                    $empresaGeral->emp_frqmst = $request->emp_frqmst;
                    $empresaGeral->emp_frq = '';
                }

                if ($request->emp_frqcmp == 'sim') {
                    $empresaGeral->emp_frqcmp = 'x';
                } else {
                    $empresaGeral->emp_frqcmp = '';
                }

                if ($request->emp_altlmt == 'sim') {
                    $empresaGeral->emp_altlmt = 'x';
                } else {
                    $empresaGeral->emp_altlmt = '';
                }

                $empresaGeral->emp_tpbolet = $request->emp_tpbolet;
                $empresaGeral->tp_plano = $request->tp_plano;
                $empresaGeral->emp_adqrnt = $request->emp_adqrnt;
                $empresaGeral->emp_meiocom = $request->emp_meiocom;
                $empresaGeral->vlr_imp = formatarTextoParaDecimal($request->vlr_imp);
                $empresaGeral->dtvenc_imp = Carbon::createFromFormat('d/m/Y', $request->dtvenc_imp)->format('Y-m-d');
                $empresaGeral->cond_pgto = $request->cond_pgto;
                $empresaGeral->vlr_mens = formatarTextoParaDecimal($request->vlr_mens);
                $empresaGeral->dtvenc_mens = Carbon::createFromFormat('d/m/Y', $request->dtvenc_mens)->format('Y-m-d');
                $empresaGeral->emp_cep = removerMascaraCEP($request->emp_cep);
                $empresaGeral->emp_end = $request->emp_end;
                $empresaGeral->emp_endnum = $request->emp_endnum;
                $empresaGeral->emp_endcmp = $request->emp_endcmp;
                $empresaGeral->emp_endbair = $request->emp_endbair;
                $empresaGeral->emp_endpais = $request->emp_endpais;
                $empresaGeral->emp_endest = $request->emp_endest;
                $empresaGeral->emp_endcid = $request->emp_endcid;
                $empresaGeral->emp_resplg = $request->emp_resplg;
                $empresaGeral->emp_emailrp = $request->emp_emailrp;
                $empresaGeral->emp_celrp = removerMascaraTelefone($request->emp_celrp);
                $empresaGeral->emp_respcm = $request->emp_respcm;
                $empresaGeral->emp_emailcm = $request->emp_emailcm;
                $empresaGeral->emp_celcm = removerMascaraTelefone($request->emp_celcm);
                $empresaGeral->emp_respfi = $request->emp_respfi;
                $empresaGeral->emp_emailfi = $request->emp_emailfi;
                $empresaGeral->emp_celfi = removerMascaraTelefone($request->emp_celfi);
                $empresaGeral->emp_pagweb = $request->emp_pagweb;
                $empresaGeral->emp_rdsoc = $request->emp_rdsoc;

                $empresaGeral->emp_integra = $input['emp_integra'];
                $empresaGeral->emp_checkb = $input['emp_checkb'];
                $empresaGeral->emp_checkm = $input['emp_checkm'];
                $empresaGeral->emp_checkc = $input['emp_checkc'];
                $empresaGeral->emp_reemb = $input['emp_reemb'];

                if ($request->hasFile('logoFile')) {
                    $empresaId = $empresaGeral->emp_id;
                    // Remove o arquivo anterior, se existir
                    if ($empresaGeral->logo_path && Storage::disk('public')->exists($empresaGeral->logo_path)) {
                        Storage::disk('public')->delete($empresaGeral->logo_path);
                    }
                    $file = $request->file('logoFile');
                    $extension = $file->getClientOriginalExtension();
                    $filename = "logo.$extension";
                    $path = $file->storeAs("logos/empresa_$empresaId", $filename, 'public');
                    $empresaGeral->logo_path = $path;
                }

                $empresaGeral->dthr_cr = Carbon::now();
                $empresaGeral->modificador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $empresaGeral->dthr_ch = Carbon::now();

                $empresaParam->save();
                $empresaGeral->save();

                $this->gravaTaxas($request, $empresaGeral->emp_id);

                DB::commit();
            }

            // Session::flash('success', "Empresa atualizada com sucesso.");

            // Session::flash("idModeloInserido", $id);
            return response()->json([
                'message' => 'Empresa atualizada com sucesso.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $empresa = Empresa::find($id);
            if ($empresa) {
                $empresa->emp_sts = EmpresaStatusEnum::EXCLUIDO;
                $empresa->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Excluído com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $e->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function inactive($id)
    {
        try {

            $empresa = Empresa::find($id);
            if ($empresa) {
                $empresa->emp_sts = EmpresaStatusEnum::INATIVO;
                $empresa->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Inativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $e->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function active($id)
    {
        try {

            $empresa = Empresa::find($id);
            if ($empresa) {
                $empresa->emp_sts = EmpresaStatusEnum::ATIVO;
                $empresa->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Ativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $e->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO EMPRESA FRANQUEADORA
    // Neste campos, mostraremos como opção de filtro, apenas as empresas que são franqueadoras
    public function getObterEmpresasFranqueadoras(Request $request)
    {

        $parametro = $request != null ? $request->all()['parametro'] : '';
        $parametro = removerCNPJ($parametro);
        $parametro = removerMascaraCEP($parametro);
        $parametro = removerMascaraTelefone($parametro);

        return Empresa::select(DB::raw('emp_id as id, emp_id, emp_cnpj, UPPER(emp_nmult) text'))
            ->whereRaw("(emp_nmult LIKE '%$parametro%' OR emp_cnpj LIKE '%$parametro%' OR emp_id LIKE '%$parametro%') AND emp_frq = 'x'")
            ->get()
            ->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO EMPRESA
    public function getObterEmpresas(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'emp_nfant';

        if (empty($parametro)) {
            return [];
        }

        if (! empty($request->campo)) {
            $campo = $request->campo;
        }

        $query = Empresa::select(DB::raw('emp_id as id, emp_id, emp_cnpj, UPPER(' . $campo . ') text'))
            ->where(function ($q) use ($campo, $parametro) {
                $q->where($campo, 'like', "%$parametro%")
                    ->orWhere('emp_cnpj', 'like', "%$parametro%")
                    ->orWhere('emp_id', 'like', "%$parametro%");
            });

        // Se o filtro cod_franqueadora estiver preenchido, filtra pelo campo emp_frqmst
        if (! empty($request->cod_franqueadora)) {
            $query->Where('emp_frqmst', $request->cod_franqueadora);
        }

        return $query->get()->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO EMPRESA
    public function getObterEmpresasNmult(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'emp_nmult';

        if (empty($parametro)) {
            return [];
        }

        if (! empty($request->campo)) {
            $campo = $request->campo;
        }

        $query = Empresa::select(DB::raw('emp_id as id, emp_id, emp_cnpj, UPPER(' . $campo . ') text'))
            ->where(function ($q) use ($campo, $parametro) {
                $q->where($campo, 'like', "%$parametro%")
                    ->orWhere('emp_cnpj', 'like', "%$parametro%")
                    ->orWhere('emp_id', 'like', "%$parametro%");
            });

        // Se o filtro cod_franqueadora estiver preenchido, filtra pelo campo emp_frqmst
        if (! empty($request->cod_franqueadora)) {
            $query->Where('emp_frqmst', $request->cod_franqueadora);
        }

        return $query->get()->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DE USUÁRIOS
    public function getObterUsers(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';

        if (empty($parametro)) {
            return [];
        }

        return User::select(DB::raw('user_id as id, user_id, user_cpf, UPPER(user_name) text'))
            ->where(function ($q) use ($parametro) {
                $q->where('user_name', 'like', "%$parametro%")
                    ->orWhere('user_cpf', 'like', "%$parametro%")
                    ->orWhere('user_id', $parametro);
            })
            ->get()
            ->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DE PAÍSES
    public function getObterPais(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';

        return Pais::select(DB::raw('pais as id, pais, UPPER(pais_desc) text'))
            ->where(function ($q) use ($parametro) {
                $q->where('pais', 'like', "%$parametro%")
                    ->orWhere('pais_desc', 'like', "%$parametro%");
            })
            ->get()
            ->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DE ESTADOS
    public function getObterEstado(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';

        if (empty($request->pais)) {
            return [];
        }

        return Estados::select(DB::raw('estado as id, estado, UPPER(estado_desc) text'))
            ->where(function ($q) use ($parametro) {
                $q->where('estado', 'like', "%$parametro%")
                    ->orWhere('estado_desc', 'like', "%$parametro%");
            })
            ->where('estado_pais', $request->pais)
            ->get()
            ->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DE CIDADES
    public function getObterCidade(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        if (empty($request->estado)) {
            return [];
        }

        return Cidade::select(DB::raw('cidade_ibge as id, cidade_ibge, UPPER(cidade_desc) text'))
            ->where(function ($q) use ($parametro) {
                $q->where('cidade_ibge', 'like', "%$parametro%")
                    ->orWhere('cidade_desc', 'like', "%$parametro%");
            })
            ->where('cidade_est', $request->estado)
            ->get()
            ->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DE ESTADOS POR PAÍS
    public function getCityEstPais(Request $request)
    {
        $cidade = Cidade::where('cidade_ibge', $request['parametro'])->first();

        $data = [
            'cidade' => ['id' => $cidade->cidade, 'text' => $cidade->cidade_ibge . ' - ' . $cidade->cidade_desc],
            'estado' => ['id' => $cidade->estado->estado, 'text' => $cidade->estado->estado_desc . ' - ' . $cidade->estado->estado],
            'pais'   => ['id' => $cidade->estado->pais->pais, 'text' => $cidade->estado->pais->pais . ' - ' . $cidade->estado->pais->pais_desc],
        ];

        return $data;
    }

    // FUNÇÃO QUE VALIDA TAXAS
    public function validaTaxas(Request $request): array
    {

        $hasError = false;
        $error_list = [];
        if ($request->has('tax_categ_avista')) {
            foreach ($request->get('tax_categ_avista') as $key => $value) {

                if (
                    ! empty(formatarTextoParaDecimal($value['parc_de'])) && ! empty(formatarTextoParaDecimal($value['parc_ate'])) && ! empty(formatarTextoParaDecimal($value['taxa']))
                ) {

                    $last = Arr::last($request->tax_categ_avista);

                    if (intval($request->card_posparc) != intval($last['parc_ate'])) {
                        $error_list['message'][$value['categ']] = ['"multban"' . 'Para a parametrização da primeira parcela à vista, falta a taxa para a parcela de ' . $request->card_posparc . 'X'];
                        $hasError = true;
                    }
                } else {

                    if (empty(formatarTextoParaDecimal($value['parc_de']))) {
                        $error_list['message'][$value['categ'] . '_parc_de'] = ['"multban"' . "O campo 'Parcela de' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['parc_ate']))) {
                        $error_list['message'][$value['categ'] . '_parc_ate'] = ['"multban"' . "O campo 'Parcela até' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['taxa']))) {
                        $error_list['message'][$value['categ'] . '_taxa'] = ['"multban"' . "O campo 'Taxa' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }
                }
            }
        }
        if ($request->has('tax_categ_30')) {
            foreach ($request->get('tax_categ_30') as $key => $value) {

                if (
                    ! empty(formatarTextoParaDecimal($value['parc_de'])) && ! empty(formatarTextoParaDecimal($value['parc_ate'])) && ! empty(formatarTextoParaDecimal($value['taxa']))
                ) {

                    $last = Arr::last($request->tax_categ_30);

                    if (intval($request->card_posparc) != intval($last['parc_ate'])) {
                        $error_list['message'][$value['categ']] = ['"multban"' . 'Para a parametrização da primeira parcela para 30 dias, falta a taxa para a parcela de ' . $request->card_posparc . 'X'];
                        $hasError = true;
                    }
                } else {
                    if (empty(formatarTextoParaDecimal($value['parc_de']))) {
                        $error_list['message'][$value['categ'] . '_parc_de'] = ['"multban"' . "O campo 'Parcela de' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['parc_ate']))) {
                        $error_list['message'][$value['categ'] . '_parc_ate'] = ['"multban"' . "O campo 'Parcela até' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['taxa']))) {
                        $error_list['message'][$value['categ'] . '_taxa'] = ['"multban"' . "O campo 'Taxa' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }
                }
            }
        }
        if ($request->has('tax_categ_60')) {
            foreach ($request->get('tax_categ_60') as $key => $value) {

                if (
                    ! empty(formatarTextoParaDecimal($value['parc_de'])) && ! empty(formatarTextoParaDecimal($value['parc_ate'])) && ! empty(formatarTextoParaDecimal($value['taxa']))
                ) {

                    $last = Arr::last($request->tax_categ_60);

                    if (intval($request->card_posparc) != intval($last['parc_ate'])) {
                        $error_list['message'][$value['categ']] = ['"multban"' . 'Para a parametrização da primeira parcela para 60 dias, falta a taxa para a parcela de ' . $request->card_posparc . 'X'];
                        $hasError = true;
                    }
                } else {
                    if (empty(formatarTextoParaDecimal($value['parc_de']))) {
                        $error_list['message'][$value['categ'] . '_parc_de'] = ['"multban"' . "O campo 'Parcela de' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['parc_ate']))) {
                        $error_list['message'][$value['categ'] . '_parc_ate'] = ['"multban"' . "O campo 'Parcela até' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['taxa']))) {
                        $error_list['message'][$value['categ'] . '_taxa'] = ['"multban"' . "O campo 'Taxa' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }
                }
            }
        }
        if ($request->has('tax_categ_90')) {
            foreach ($request->get('tax_categ_90') as $key => $value) {

                if (
                    ! empty(formatarTextoParaDecimal($value['parc_de'])) && ! empty(formatarTextoParaDecimal($value['parc_ate'])) && ! empty(formatarTextoParaDecimal($value['taxa']))
                ) {

                    $last = Arr::last($request->tax_categ_90);

                    if (intval($request->card_posparc) != intval($last['parc_ate'])) {
                        $error_list['message'][$value['categ']] = ['"multban"' . 'Para a parametrização da primeira parcela para 90 dias, falta a taxa para a parcela de ' . $request->card_posparc . 'X'];
                        $hasError = true;
                    }
                } else {
                    if (empty(formatarTextoParaDecimal($value['parc_de']))) {
                        $error_list['message'][$value['categ'] . '_parc_de'] = ['"multban"' . "O campo 'Parcela de' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['parc_ate']))) {
                        $error_list['message'][$value['categ'] . '_parc_ate'] = ['"multban"' . "O campo 'Parcela até' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }

                    if (empty(formatarTextoParaDecimal($value['taxa']))) {
                        $error_list['message'][$value['categ'] . '_taxa'] = ['"multban"' . "O campo 'Taxa' não pode ficar vazio ou zerado."];
                        $hasError = true;
                    }
                }
            }
        }

        return ['hasError' => $hasError, 'error_list' => $error_list];
    }

    // FUNÇÃO QUE GRAVA TAXAS
    public function gravaTaxas(Request $request, $emp_id)
    {

        $hasError = false;
        $error_list = [];

        EmpresaTaxpos::where('emp_id', $emp_id)->delete();

        if ($request->has('tax_categ_avista')) {
            foreach ($request->get('tax_categ_avista') as $key => $value) {

                $taxa = new EmpresaTaxpos;
                $taxa->emp_id = $emp_id;
                $taxa->tax_categ = '' . $value['categ'] . '';
                $taxa->parc_de = intval($value['parc_de']);
                $taxa->parc_ate = intval($value['parc_ate']);
                $taxa->tax = formatarTextoParaDecimal($value['taxa']);
                $taxa->dthr_cr = Carbon::now();
                $taxa->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $taxa->dthr_ch = Carbon::now();
                $taxa->save();
            }
        }
        if ($request->has('tax_categ_30')) {
            foreach ($request->get('tax_categ_30') as $key => $value) {

                $taxa = new EmpresaTaxpos;
                $taxa->emp_id = $emp_id;
                $taxa->tax_categ = '' . $value['categ'] . '';
                $taxa->parc_de = intval($value['parc_de']);
                $taxa->parc_ate = intval($value['parc_ate']);
                $taxa->tax = formatarTextoParaDecimal($value['taxa']);
                $taxa->dthr_cr = Carbon::now();
                $taxa->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $taxa->dthr_ch = Carbon::now();
                $taxa->save();
            }
        }
        if ($request->has('tax_categ_60')) {
            foreach ($request->get('tax_categ_60') as $key => $value) {
                $taxa = new EmpresaTaxpos;
                $taxa->emp_id = $emp_id;
                $taxa->tax_categ = '' . $value['categ'] . '';
                $taxa->parc_de = intval($value['parc_de']);
                $taxa->parc_ate = intval($value['parc_ate']);
                $taxa->tax = formatarTextoParaDecimal($value['taxa']);
                $taxa->dthr_cr = Carbon::now();
                $taxa->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $taxa->dthr_ch = Carbon::now();
                $taxa->save();
            }
        }
        if ($request->has('tax_categ_90')) {
            foreach ($request->get('tax_categ_90') as $key => $value) {
                $taxa = new EmpresaTaxpos;
                $taxa->emp_id = $emp_id;
                $taxa->tax_categ = '' . $value['categ'] . '';
                $taxa->parc_de = intval($value['parc_de']);
                $taxa->parc_ate = intval($value['parc_ate']);
                $taxa->tax = formatarTextoParaDecimal($value['taxa']);
                $taxa->dthr_cr = Carbon::now();
                $taxa->criador = \Illuminate\Support\Facades\Auth::user()->user_id;
                $taxa->dthr_ch = Carbon::now();
                $taxa->save();
            }
        }

        return ['hasError' => $hasError, 'error_list' => $error_list];
    }

    // FUNÇÃO QUE RETORNA AS EMPRESAS AO CLICAR EM PESQUISAR
    public function getObterGridPesquisa(Request $request)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $emp_id = '';
        $emp_id_frqmst = '';
        $emp_id_nmult = '';
        $status = '';

        $data = new Collection;

        if (! empty($request->cod_franqueadora)) {
            $emp_id_frqmst = $request->cod_franqueadora;
        }

        if (! empty($request->empresa_id)) {
            $emp_id = $request->empresa_id;
        }

        if (! empty($request->nome_multban)) {
            $emp_id_nmult = $request->nome_multban;
        }

        if (! empty($request->emp_sts)) {
            $status = $request->emp_sts;
        }

        // PESQUISA A EMPRESA PELOS FILTROS SELECIONADOS
        $query = Empresa::query();

        if (! empty($emp_id)) {
            if (is_numeric($emp_id) && intval($emp_id) > 0) {
                $query->where('emp_id', '=', $emp_id);
            } else {
                $query->where('emp_rzsoc', 'like', '%' . $emp_id . '%');
            }
        }

        if (! empty($emp_id_frqmst)) {
            if (is_numeric($emp_id_frqmst) && intval($emp_id_frqmst) > 0) {
                $query->where('emp_frqmst', '=', $emp_id_frqmst);
            } else {
                $query->where('emp_rzsoc', 'like', '%' . $emp_id_frqmst . '%');
            }
        }

        if (! empty($emp_id_nmult)) {
            if (is_numeric($emp_id_nmult) && intval($emp_id_nmult) > 0) {
                $query->where('emp_id', '=', $emp_id_nmult);
            } else {
                $query->where('emp_rzsoc', 'like', '%' . $emp_id_nmult . '%');
            }
        }

        if (! empty($status)) {
            $query->where('emp_sts', '=', $status);
        }

        if (! empty($request->empresa_cnpj)) {
            $query->where('emp_cnpj', 'like', '%' . removerCNPJ($request->empresa_cnpj) . '%');
        }

        // RESULTADO FINAL DA PESQUISA
        $data = $query->get();

        $this->permissions = Auth::user()->permissions->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('empresa.edit', $this->permissions)) {
                    $btn .= '<a href="empresa/' . $row->emp_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $disabled = '';
                if ($row->status->emp_sts == EmpresaStatusEnum::ATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="empresa" data-id="' . $row->emp_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

                $disabled = '';
                if ($row->status->emp_sts == EmpresaStatusEnum::INATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="empresa" data-id="' . $row->emp_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

                if (in_array('empresa.destroy', $this->permissions)) {
                    $disabled = '';
                    if ($row->status->emp_sts == EmpresaStatusEnum::EXCLUIDO) {
                        $disabled = 'disabled';
                    }
                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1" ' . $disabled . ' id="delete_grid_id" data-url="empresa" data-id="' . $row->emp_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }
                $btn .= '';

                return $btn;
            })->editColumn('emp_cnpj', function ($row) {
                $badge = strlen($row->emp_cnpj) == 14 ? formatarCNPJ($row->emp_cnpj) : formatarCPF($row->emp_cnpj);

                return $badge;
            })->editColumn('emp_sts', function ($row) {
                $badge = '';
                if (! empty($row->status)) {

                    switch ($row->status->emp_sts) {

                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $row->status->emp_sts_desc . '</span>';
                            break;
                        case 'NA':
                        case 'IN':
                        case 'ON':
                            $badge = '<span class="badge badge-warning">' . $row->status->emp_sts_desc . '</span>';
                            break;
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $row->status->emp_sts_desc . '</span>';
                            break;
                    }
                }

                return $badge;
            })->editColumn('emp_id', function ($row) {
                $id = str_pad($row->emp_id, 5, '0', STR_PAD_LEFT);

                return $id;
            })
            ->rawColumns(['action', 'emp_cnpj', 'emp_sts'])
            ->make(true);
    }
}
