<?php
require_once '../../config/database.php';

class Configuracoes {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getConfiguracoes() {
        $query = "SELECT * FROM configuracoes LIMIT 1";
        $result = $this->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function atualizarConfiguracoes($dados) {
        $query = "UPDATE configuracoes SET 
            nome_estabelecimento = ?,
            email_contato = ?,
            telefone = ?,
            endereco = ?,
            taxa_entrega = ?,
            raio_entrega = ?,
            tempo_medio_entrega = ?,
            entrega_gratis = ?,
            valor_minimo_entrega_gratis = ?,
            horario_abertura = ?,
            horario_fechamento = ?,
            status_estabelecimento = ?
        WHERE id = 1";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bind_param(
            "ssssdiiidssi",
            $dados['nome_estabelecimento'],
            $dados['email_contato'],
            $dados['telefone'],
            $dados['endereco'],
            $dados['taxa_entrega'],
            $dados['raio_entrega'],
            $dados['tempo_medio_entrega'],
            $dados['entrega_gratis'],
            $dados['valor_minimo_entrega_gratis'],
            $dados['horario_abertura'],
            $dados['horario_fechamento'],
            $dados['status_estabelecimento']
        );

        return $stmt->execute();
    }
} 