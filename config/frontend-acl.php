<?php

return [
    /**
     * Define which frontend keys depend on which permissions.
     * Example usage in Blade: @canView('clientes')
     */
    'map' => [
        'clientes'    => 'cliente.index',
        'usuarios'    => 'usuario.index',
        'agendamentos' => 'agendamento.index',
        'empresas'    => 'empresa.index',
        'produtos'    => 'produto.index',
        'relatorios'  => 'relatorios.index',
    ],
];

