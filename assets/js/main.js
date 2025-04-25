// Função para adicionar produto ao carrinho
function adicionarAoCarrinho(produtoId) {
    // Verifica se já existe um carrinho na sessão
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    
    // Verifica se o produto já está no carrinho
    const produtoExistente = carrinho.find(item => item.id === produtoId);
    
    if (produtoExistente) {
        // Se o produto já existe, incrementa a quantidade
        produtoExistente.quantidade += 1;
    } else {
        // Se não existe, adiciona novo item
        carrinho.push({
            id: produtoId,
            quantidade: 1
        });
    }
    
    // Salva o carrinho atualizado no localStorage
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    
    // Mostra mensagem de sucesso
    alert('Produto adicionado ao carrinho!');
    
    // Atualiza o contador do carrinho
    atualizarContadorCarrinho();
}

// Função para atualizar o contador do carrinho
function atualizarContadorCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
    
    // Atualiza o contador no navbar
    const contadorCarrinho = document.getElementById('contador-carrinho');
    if (contadorCarrinho) {
        contadorCarrinho.textContent = totalItens;
        contadorCarrinho.style.display = totalItens > 0 ? 'inline' : 'none';
    }
}

// Inicializa o contador do carrinho quando a página carrega
document.addEventListener('DOMContentLoaded', atualizarContadorCarrinho);

// Funções de filtro e busca
document.addEventListener('DOMContentLoaded', function() {
    const buscaInput = document.getElementById('busca');
    const ordenarSelect = document.getElementById('ordenar');
    
    // Função para filtrar produtos
    function filtrarProdutos() {
        const termoBusca = buscaInput.value.toLowerCase();
        const ordenacao = ordenarSelect.value;
        
        const produtos = document.querySelectorAll('.produto-item');
        let produtosFiltrados = Array.from(produtos);
        
        // Filtra por busca
        if (termoBusca) {
            produtosFiltrados = produtosFiltrados.filter(produto => {
                const nome = produto.dataset.nome.toLowerCase();
                return nome.includes(termoBusca);
            });
        }
        
        // Ordena os produtos
        produtosFiltrados.sort((a, b) => {
            switch (ordenacao) {
                case 'nome':
                    return a.dataset.nome.localeCompare(b.dataset.nome);
                case 'preco_asc':
                    return parseFloat(a.dataset.preco) - parseFloat(b.dataset.preco);
                case 'preco_desc':
                    return parseFloat(b.dataset.preco) - parseFloat(a.dataset.preco);
                default:
                    return 0;
            }
        });
        
        // Atualiza a exibição
        const listaProdutos = document.getElementById('lista-produtos');
        listaProdutos.innerHTML = '';
        produtosFiltrados.forEach(produto => {
            listaProdutos.appendChild(produto);
        });
    }
    
    // Event listeners
    buscaInput.addEventListener('input', filtrarProdutos);
    ordenarSelect.addEventListener('change', filtrarProdutos);
    
    // Inicializa o contador do carrinho
    atualizarContadorCarrinho();
}); 