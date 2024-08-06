# EyeOfTiger

**Versão:** 1.0.0

EyeOfTiger é uma aplicação web projetada para monitorar e gerenciar o acesso à sua aplicação principal. A aplicação oferece funcionalidades de firewall, CAPTCHA e um painel de controle para gerenciar a lista de IPs bloqueados e visualizar logs de acesso.

## Funcionalidades

- **Firewall**: Bloqueia acessos de IPs na blacklist.
- **CAPTCHA**: Exige resolução de CAPTCHA para acessos suspeitos.
- **Redirecionamento**: Redireciona acessos não autorizados para uma página de lixo, configurável via painel.
- **Painel de Controle**: Adiciona/Remove IPs da blacklist e visualiza logs de acesso.
- **Autenticação**: Protege o painel de controle com login básico (usuário: admin, senha: admin).

## Estrutura de Arquivos

- `data/blacklist.txt`: Lista de IPs bloqueados.
- `data/informations.txt`: Logs de acesso.
- `data/redirect.txt`: URL de redirecionamento para acessos não autorizados.
- `index.php`: Página principal da aplicação.
- `EyeOfTiger.php`: Painel de controle.

## Instalação

1. Clone o repositório.
2. Certifique-se de que o servidor web tenha permissão de leitura e escrita na pasta `data/`.
3. Acesse `index.php` para verificar a aplicação principal.
4. Acesse `EyeOfTiger.php` para o painel de controle (usuário: admin, senha: admin).

## Uso

### Página Principal (`index.php`)

A página principal verifica os acessos, registra logs e redireciona para o CAPTCHA se necessário. Se o CAPTCHA for resolvido corretamente, o acesso é permitido.

### Painel de Controle (`EyeOfTiger.php`)

O painel permite gerenciar a blacklist e visualizar os logs de acesso. Além disso, é possível configurar a URL de redirecionamento para acessos não autorizados.
