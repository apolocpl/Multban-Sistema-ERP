<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    private $conta;

    private $user;

    public function setConta($value)
    {
        $this->conta = $value;
    }

    public function getConta()
    {
        return $this->conta;
    }

    public function index()
    {
        if (Auth::check()) {
            return view('home', $this->obterParametroHome());
        } else {
            return view('auth.login');
        }
    }

    public function maisVendidos()
    {
        $maisVendidos = [];

        $this->setConta(0);

        return $this->datatablesMaisVendidos($maisVendidos);
    }

    public function datatablesMaisVendidos($data)
    {

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('porcentagem', function ($row) {
                $badge = '<span class="badge badge-success">' . number_format((($row->quantidade * 100) / $this->getConta()), 2) . '%</span>';
                if ((($row->quantidade * 100) / $this->getConta()) <= 10) {
                    $badge = '<span class="badge badge-danger">' . number_format((($row->quantidade * 100) / $this->getConta()), 2) . '%</span>';
                } elseif (
                    (($row->quantidade * 100) / $this->getConta()) > 10 &&
                    (($row->quantidade * 100) / $this->getConta()) <= 20
                ) {
                    $badge = '<span class="badge badge-warning">' . number_format((($row->quantidade * 100) / $this->getConta()), 2) . '%</span>';
                } elseif (
                    number_format((($row->quantidade * 100) / $this->getConta()), 2) > 20 &&
                    number_format((($row->quantidade * 100) / $this->getConta()), 2) <= 50
                ) {
                    $badge = '<span class="badge badge-info">' . number_format((($row->quantidade * 100) / $this->getConta()), 2) . '%</span>';
                }

                return $badge;
            })
            ->rawColumns(['porcentagem'])
            ->make(true);
    }

    public function obterParametroHome()
    {
        $this->user = Auth::user();

        $vendas = 0;

        $produtosVendidos = 0;

        $months = [
            'January'   => 0.00,
            'February'  => 0.00,
            'March'     => 0.00,
            'April'     => 0.00,
            'May'       => 0.00,
            'June'      => 0.00,
            'July'      => 0.00,
            'August'    => 0.00,
            'September' => 0.00,
            'October'   => 0.00,
            'November'  => 0.00,
            'December'  => 0.00,
        ];

        $months = array_map('floatval', $months);
        // dd($repositorioDeFinanceiroLancamento->obterParaDashboard());

        $cadascli = [
            'January'   => 0,
            'February'  => 0,
            'March'     => 0,
            'April'     => 0,
            'May'       => 0,
            'June'      => 0,
            'July'      => 0,
            'August'    => 0,
            'September' => 0,
            'October'   => 0,
            'November'  => 0,
            'December'  => 0,
        ];

        return [
            'quantidadeCliente'     => 0,
            'quantidadeProduto'     => 0,
            'dashboardMovimentacao' => $this->obterParaDashboard(),
            'maisVendidos'          => [],
            'vendasPorMes'          => collect($months),
            'vendas'                => $vendas,
            'produtosVendidos'      => $produtosVendidos,
            'cadascli'              => collect($cadascli),
        ];
    }

    public function obterParaDashboard()
    {
        $monthsReceita = [
            'January'   => 0.00,
            'February'  => 0.00,
            'March'     => 0.00,
            'April'     => 0.00,
            'May'       => 0.00,
            'June'      => 0.00,
            'July'      => 0.00,
            'August'    => 0.00,
            'September' => 0.00,
            'October'   => 0.00,
            'November'  => 0.00,
            'December'  => 0.00,
        ];

        $monthsDespesa = [
            'January'   => 0.00,
            'February'  => 0.00,
            'March'     => 0.00,
            'April'     => 0.00,
            'May'       => 0.00,
            'June'      => 0.00,
            'July'      => 0.00,
            'August'    => 0.00,
            'September' => 0.00,
            'October'   => 0.00,
            'November'  => 0.00,
            'December'  => 0.00,
        ];

        $monthsTotal = [
            'January'   => 0.00,
            'February'  => 0.00,
            'March'     => 0.00,
            'April'     => 0.00,
            'May'       => 0.00,
            'June'      => 0.00,
            'July'      => 0.00,
            'August'    => 0.00,
            'September' => 0.00,
            'October'   => 0.00,
            'November'  => 0.00,
            'December'  => 0.00,
        ];

        $receita = 0;
        $despesa = 0;
        $total = 0;

        $monthsReceita = array_map('floatval', $monthsReceita);
        $monthsDespesa = array_map('floatval', $monthsDespesa);
        $monthsTotal = array_map('floatval', $monthsTotal);
        // dd($monthsTotal);
        // dd([
        //     'receita' => $monthsReceita,
        //     'despesa' => $monthsDespesa,
        //     'total' => $total,
        //     'monthsReceita' => $monthsReceita,
        //     'monthsDespesa' => $monthsDespesa,
        //     'monthsTotal' => $monthsTotal
        //     ]);

        return [
            'receita'       => 0,
            'despesa'       => 0,
            'total'         => $total,
            'monthsReceita' => collect($monthsReceita)->flatten()->all(),
            'monthsDespesa' => collect($monthsDespesa)->flatten()->all(),
            'monthsTotal'   => collect($monthsTotal)->flatten()->all(),
        ];
    }
}
