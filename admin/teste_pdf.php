<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../vendor/autoload.php');

try {
    // Verificar se há saída antes de começar
    if (ob_get_length()) ob_clean();
    
    // Usar diretório temporário específico do projeto
    $tempDir = __DIR__ . '/../tmp';
    if (!is_writable($tempDir)) {
        throw new Exception("Diretório temporário não tem permissão de escrita: " . $tempDir);
    }

    // Configuração básica do Mpdf
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'tempDir' => $tempDir
    ]);

    // Conteúdo simples
    $html = '<h1>Teste PDF</h1>';
    $html .= '<p>Se você está vendo isso, o Mpdf está funcionando!</p>';
    $html .= '<p>Data: ' . date('d/m/Y H:i:s') . '</p>';
    $html .= '<p>Diretório temporário: ' . $tempDir . '</p>';
    
    // Escrever o conteúdo
    $mpdf->WriteHTML($html);
    
    // Limpar qualquer saída anterior
    if (ob_get_length()) ob_clean();
    
    // Definir cabeçalhos
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="teste.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Gerar PDF
    $mpdf->Output('teste.pdf', 'D');
    exit();
} catch (Exception $e) {
    // Limpar qualquer saída anterior
    if (ob_get_length()) ob_clean();
    
    // Mostrar erro detalhado
    echo "Erro ao gerar PDF:<br>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "Diretório temporário: " . $tempDir . "<br>";
    echo "Permissões do diretório temporário: " . substr(sprintf('%o', fileperms($tempDir)), -4);
}
?> 