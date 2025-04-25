# Sistema de Delivery

Sistema completo de delivery com painel administrativo para gerenciamento de pedidos, produtos e clientes.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache/Nginx com mod_rewrite habilitado
- Composer (para gerenciamento de dependências)

## Instalação

1. Clone o repositório:
```bash
git clone [url-do-repositorio]
cd delivery
```

2. Importe o banco de dados:
```bash
mysql -u root -p < database.sql
```

3. Configure o arquivo `config/database.php` com suas credenciais do banco de dados.

4. Certifique-se de que o Apache/Nginx tem permissão de escrita nas pastas:
```bash
chmod -R 777 uploads/
```

5. Acesse o sistema:
- Frontend: http://localhost/delivery
- Admin: http://localhost/delivery/admin
  - Email: admin@delivery.com
  - Senha: admin123

## Estrutura do Projeto

```
delivery/
├── admin/              # Painel administrativo
├── assets/            # Arquivos estáticos (CSS, JS, imagens)
├── config/            # Arquivos de configuração
├── uploads/           # Uploads de imagens
├── index.php          # Página inicial
├── .htaccess          # Configurações do Apache
└── README.md          # Documentação
```

## Funcionalidades

### Frontend
- Catálogo de produtos
- Carrinho de compras
- Área do cliente
- Checkout de pedidos

### Painel Administrativo
- Dashboard com estatísticas
- Gerenciamento de produtos
- Gerenciamento de pedidos
- Gerenciamento de clientes
- Relatórios

## Segurança

- Senhas criptografadas
- Proteção contra SQL Injection
- Validação de formulários
- Controle de acesso por sessão

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE para detalhes. 