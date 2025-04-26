<?php
require_once '../config/database.php';
require_once 'classes/Impressora.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

function gerarConteudoNota($pedido, $tipo_impressao) {
    $conteudo = "";
    
    if ($tipo_impressao === 'nota_fiscal') {
        $conteudo .= "NOTA FISCAL\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Número do Pedido: #" . $pedido['id'] . "\n";
        $conteudo .= "Data: " . date('d/m/Y H:i', strtotime($pedido['data'])) . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Cliente: " . $pedido['cliente_nome'] . "\n";
        $conteudo .= "Telefone: " . $pedido['telefone'] . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "ENDEREÇO DE ENTREGA\n";
        $conteudo .= $pedido['endereco_entrega'] . ", " . $pedido['numero_entrega'] . "\n";
        if (!empty($pedido['complemento_entrega'])) {
            $conteudo .= "Complemento: " . $pedido['complemento_entrega'] . "\n";
        }
        $conteudo .= $pedido['bairro_entrega'] . "\n";
        $conteudo .= $pedido['cidade_entrega'] . " - " . $pedido['cep_entrega'] . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "RESUMO DO PEDIDO\n";
        $conteudo .= "Valor dos Itens: R$ " . number_format($pedido['valor_total'], 2, ',', '.') . "\n";
        $conteudo .= "Taxa de Entrega: R$ " . number_format($pedido['taxa_entrega'], 2, ',', '.') . "\n";
        $conteudo .= "Total: R$ " . number_format($pedido['total'], 2, ',', '.') . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "FORMA DE PAGAMENTO\n";
        $conteudo .= ucfirst($pedido['forma_pagamento']);
        if ($pedido['forma_pagamento'] === 'dinheiro' && !empty($pedido['troco_para'])) {
            $conteudo .= "\nTroco para: R$ " . number_format($pedido['troco_para'], 2, ',', '.');
        }
        $conteudo .= "\n----------------------------------------\n";
        $conteudo .= "Status: " . ucfirst($pedido['status']) . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Obrigado pela preferência!\n";
    } else {
        $conteudo .= "COMPROVANTE DE PEDIDO\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Pedido #" . $pedido['id'] . "\n";
        $conteudo .= date('d/m/Y H:i', strtotime($pedido['data'])) . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Cliente: " . $pedido['cliente_nome'] . "\n";
        $conteudo .= "----------------------------------------\n";
        $conteudo .= "Valor Total: R$ " . number_format($pedido['total'], 2, ',', '.') . "\n";
        $conteudo .= "Forma de Pagamento: " . ucfirst($pedido['forma_pagamento']);
        if ($pedido['forma_pagamento'] === 'dinheiro' && !empty($pedido['troco_para'])) {
            $conteudo .= "\nTroco para: R$ " . number_format($pedido['troco_para'], 2, ',', '.');
        }
        $conteudo .= "\n----------------------------------------\n";
    }

    return $conteudo;
}

function gerarPDF($pedido, $tipo_impressao) {
    require_once('../vendor/autoload.php');
    
    try {
        // Verificar se há saída antes de começar
        if (ob_get_length()) ob_clean();
        
        // Usar diretório temporário específico do projeto
        $tempDir = __DIR__ . '/../tmp';
        if (!is_writable($tempDir)) {
            throw new Exception("Diretório temporário não tem permissão de escrita: " . $tempDir);
        }

        // Configuração para cupom fiscal (80mm de largura)
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 297], // 80mm de largura (tamanho padrão de cupom fiscal)
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font' => 'helvetica',
            'default_font_size' => 8,
            'tempDir' => $tempDir
        ]);

        // Conteúdo do cupom fiscal
        $content = "
        <div style='text-align: center; font-weight: bold; font-size: 10pt;'>CUPOM FISCAL</div>
        <div style='text-align: center; font-size: 8pt; margin-bottom: 5px;'>----------------------------------------</div>
        <div style='font-size: 8pt;'>Pedido: #" . htmlspecialchars($pedido['id']) . "</div>
        <div style='font-size: 8pt;'>Data: " . date('d/m/Y H:i', strtotime($pedido['data'])) . "</div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='font-size: 8pt;'>Cliente: " . htmlspecialchars($pedido['cliente_nome']) . "</div>
        <div style='font-size: 8pt;'>Tel: " . htmlspecialchars($pedido['telefone']) . "</div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='font-weight: bold; font-size: 8pt;'>ENDEREÇO DE ENTREGA</div>
        <div style='font-size: 8pt;'>" . htmlspecialchars($pedido['endereco_entrega']) . ", " . htmlspecialchars($pedido['numero_entrega']) . "</div>";

        if (!empty($pedido['complemento_entrega'])) {
            $content .= "<div style='font-size: 8pt;'>Complemento: " . htmlspecialchars($pedido['complemento_entrega']) . "</div>";
        }

        $content .= "
        <div style='font-size: 8pt;'>" . htmlspecialchars($pedido['bairro_entrega']) . "</div>
        <div style='font-size: 8pt;'>" . htmlspecialchars($pedido['cidade_entrega']) . " - " . htmlspecialchars($pedido['cep_entrega']) . "</div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='font-weight: bold; font-size: 8pt;'>RESUMO DO PEDIDO</div>
        <div style='font-size: 8pt;'>Itens: R$ " . number_format($pedido['valor_total'], 2, ',', '.') . "</div>
        <div style='font-size: 8pt;'>Entrega: R$ " . number_format($pedido['taxa_entrega'], 2, ',', '.') . "</div>
        <div style='font-size: 8pt;'>Total: R$ " . number_format($pedido['total'], 2, ',', '.') . "</div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='font-weight: bold; font-size: 8pt;'>FORMA DE PAGAMENTO</div>
        <div style='font-size: 8pt;'>" . ucfirst($pedido['forma_pagamento']);

        if ($pedido['forma_pagamento'] === 'dinheiro' && !empty($pedido['troco_para'])) {
            $content .= "<br>Troco: R$ " . number_format($pedido['troco_para'], 2, ',', '.');
        }

        $content .= "
        </div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='font-size: 8pt;'>Status: " . ucfirst($pedido['status']) . "</div>
        <div style='text-align: center; font-size: 8pt; margin: 5px 0;'>----------------------------------------</div>
        <div style='text-align: center; font-size: 8pt;'>Obrigado pela preferência!</div>
        <div style='text-align: center; font-size: 8pt; margin-top: 10px;'>----------------------------------------</div>";

        // Escrever o conteúdo no PDF
        $mpdf->WriteHTML($content);
        
        $filename = 'cupom_fiscal_' . $pedido['id'] . '.pdf';
        
        // Limpar qualquer saída anterior
        if (ob_get_length()) ob_clean();
        
        // Definir os cabeçalhos HTTP corretos
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Enviar o PDF para o navegador
        $mpdf->Output($filename, 'D');
        exit();
    } catch (Exception $e) {
        error_log('Erro ao gerar PDF: ' . $e->getMessage());
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar PDF: ' . $e->getMessage()]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'] ?? null;
    $tipo_impressao = $_POST['tipo_impressao'] ?? null;
    $impressora = $_POST['impressora'] ?? null;
    $acao = $_POST['acao'] ?? 'imprimir';

    if (!$pedido_id || !$tipo_impressao || !$impressora) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit();
    }

    try {
        $pdo = getConnection();
        
        // Buscar dados do pedido
        $stmt = $pdo->prepare("
            SELECT p.*, c.nome as cliente_nome, c.telefone
            FROM pedidos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            throw new Exception('Pedido não encontrado');
        }

        if ($acao === 'download') {
            gerarPDF($pedido, $tipo_impressao);
            exit();
        }

        // Gerar conteúdo da nota fiscal
        $conteudo = gerarConteudoNota($pedido, $tipo_impressao);

        // Enviar para impressão
        if ($impressora === 'fiscal') {
            $impressora = new ImpressoraFiscal("USB", "DARUMA");
        } else {
            $impressora = new ImpressoraTermica("USB", "EPSON");
        }

        $resultado = $impressora->imprimir($conteudo);
        $impressora->cortarPapel();
        $impressora->abrirGaveta();

        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Erro ao enviar para impressão');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
} 