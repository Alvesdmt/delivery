<?php
// Inicia a sessão
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Limpa o cookie da sessão se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redireciona para a página inicial com mensagem de sucesso
header('Location: ../index');
exit();
?> 