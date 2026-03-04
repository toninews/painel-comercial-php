<?php
use Nexa\Database\Transaction;

class ProdutosReportService
{
    public static function build()
    {
        $payload = [
            'produtos' => [],
            'total_produtos' => 0,
            'total_estoque' => '0',
        ];

        $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd
        );
        $writer = new \BaconQrCode\Writer($renderer);

        Transaction::open('painel_comercial');

        try
        {
            $produtos = Produto::all() ?: [];
            $totalEstoque = 0;

            foreach ($produtos as $produto)
            {
                $produto->barcode = $generator->getBarcode($produto->id, $generator::TYPE_CODE_128, 5, 100);
                $produto->qrcode  = $writer->writeString($produto->id . ' ' . $produto->descricao);
                $produto->preco_venda_formatado = number_format((float) $produto->preco_venda, 2, ',', '.');
                $produto->estoque_formatado = number_format((float) $produto->estoque, 0, ',', '.');
                $totalEstoque += (float) $produto->estoque;
            }

            $payload['produtos'] = $produtos;
            $payload['total_produtos'] = count($produtos);
            $payload['total_estoque'] = number_format($totalEstoque, 0, ',', '.');

            Transaction::close();

            return $payload;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }
}
