<?php
// Definir o diretório base
define('BASE_DIR', dirname(__DIR__));

// Incluir o arquivo de configuração do banco de dados
require_once BASE_DIR . '/config/database.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PedidoServer implements MessageComponentInterface {
    protected $clients;
    private $pdo;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        try {
            $this->pdo = getConnection();
        } catch (Exception $e) {
            echo "Erro ao conectar ao banco de dados: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nova conexão! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if ($data['type'] === 'pedido_atualizado') {
                $this->notificarAtualizacao();
            }
        } catch (Exception $e) {
            echo "Erro ao processar mensagem: " . $e->getMessage() . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão {$conn->resourceId} foi desconectada\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }

    private function notificarAtualizacao() {
        try {
            $message = json_encode(['type' => 'pedido_atualizado']);
            
            foreach ($this->clients as $client) {
                $client->send($message);
            }
        } catch (Exception $e) {
            echo "Erro ao notificar clientes: " . $e->getMessage() . "\n";
        }
    }
}

try {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new PedidoServer()
            )
        ),
        8080
    );

    echo "Servidor WebSocket iniciado na porta 8080\n";
    $server->run();
} catch (Exception $e) {
    echo "Erro ao iniciar o servidor: " . $e->getMessage() . "\n";
    exit(1);
}
?> 