<?php
return [
    'name' => 'Painel Comercial PHP',
    'tagline' => 'Projeto PHP clássico (server-side) evoluído com foco em arquitetura, segurança e UX',
    'default_private_page' => 'DashboardView',
    'menu' => [
        ['label' => 'Dashboard', 'class' => 'DashboardView', 'icon' => 'fa fa-home'],
        ['label' => 'Cidades', 'class' => 'CidadesFormList', 'icon' => 'fa fa-map-o'],
        ['label' => 'Fabricantes', 'class' => 'FabricantesFormList', 'icon' => 'fa fa-cogs'],
        ['label' => 'Produtos', 'class' => 'ProdutosList', 'icon' => 'fa fa-cube'],
        ['label' => 'Pessoas', 'class' => 'PessoasList', 'icon' => 'fa fa-id-card-o'],
        ['label' => 'Vendas', 'class' => 'VendasForm', 'icon' => 'fa fa-shopping-bag'],
        ['label' => 'Rel. Vendas', 'class' => 'VendasReport', 'icon' => 'fa fa-line-chart'],
        ['label' => 'Rel. Contas', 'class' => 'ContasReport', 'icon' => 'fa fa-credit-card'],
        ['label' => 'Rel. Produtos', 'class' => 'ProdutosReport', 'icon' => 'fa fa-tags'],
        ['label' => 'Rel. Pessoas', 'class' => 'PessoasReport', 'icon' => 'fa fa-address-book-o'],
        ['label' => 'Vendas mês', 'class' => 'VendasMesChart', 'icon' => 'fa fa-area-chart'],
        ['label' => 'Vendas tipo', 'class' => 'VendasTipoChart', 'icon' => 'fa fa-pie-chart'],
    ],
    'shortcuts' => [
        ['label' => 'Produtos', 'class' => 'ProdutosList'],
        ['label' => 'Pessoas', 'class' => 'PessoasList'],
        ['label' => 'Vendas', 'class' => 'VendasForm'],
    ],
];
