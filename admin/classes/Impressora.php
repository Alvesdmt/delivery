<?php

abstract class Impressora {
    protected $porta;
    protected $modelo;
    protected $conexao;

    public function __construct($porta, $modelo) {
        $this->porta = $porta;
        $this->modelo = $modelo;
        $this->conectar();
    }

    abstract protected function conectar();
    abstract public function imprimir($conteudo);
    abstract public function cortarPapel();
    abstract public function abrirGaveta();
}

class ImpressoraFiscal extends Impressora {
    protected function conectar() {
        // Implementar conexão com impressora fiscal
        // Exemplo para Daruma
        $this->conexao = fsockopen($this->porta, 9100, $errno, $errstr, 5);
        if (!$this->conexao) {
            throw new Exception("Erro ao conectar com a impressora fiscal: $errstr ($errno)");
        }
    }

    public function imprimir($conteudo) {
        if (!$this->conexao) {
            throw new Exception("Impressora não conectada");
        }

        // Comandos específicos para impressora fiscal
        $comandos = [
            "\x1B\x40", // Inicializar
            "\x1B\x61\x31", // Centralizar
            $conteudo,
            "\x1B\x64\x02", // Cortar papel
            "\x1B\x70\x00\x19\xFF" // Abrir gaveta
        ];

        foreach ($comandos as $comando) {
            fwrite($this->conexao, $comando);
        }

        return true;
    }

    public function cortarPapel() {
        fwrite($this->conexao, "\x1B\x64\x02");
    }

    public function abrirGaveta() {
        fwrite($this->conexao, "\x1B\x70\x00\x19\xFF");
    }

    public function __destruct() {
        if ($this->conexao) {
            fclose($this->conexao);
        }
    }
}

class ImpressoraTermica extends Impressora {
    protected function conectar() {
        // Implementar conexão com impressora térmica
        // Exemplo para Epson
        $this->conexao = fsockopen($this->porta, 9100, $errno, $errstr, 5);
        if (!$this->conexao) {
            throw new Exception("Erro ao conectar com a impressora térmica: $errstr ($errno)");
        }
    }

    public function imprimir($conteudo) {
        if (!$this->conexao) {
            throw new Exception("Impressora não conectada");
        }

        // Comandos específicos para impressora térmica
        $comandos = [
            "\x1B\x40", // Inicializar
            "\x1B\x61\x31", // Centralizar
            $conteudo,
            "\x1B\x64\x02", // Cortar papel
            "\x1B\x70\x00\x19\xFF" // Abrir gaveta
        ];

        foreach ($comandos as $comando) {
            fwrite($this->conexao, $comando);
        }

        return true;
    }

    public function cortarPapel() {
        fwrite($this->conexao, "\x1B\x64\x02");
    }

    public function abrirGaveta() {
        fwrite($this->conexao, "\x1B\x70\x00\x19\xFF");
    }

    public function __destruct() {
        if ($this->conexao) {
            fclose($this->conexao);
        }
    }
} 