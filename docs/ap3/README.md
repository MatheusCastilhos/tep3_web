# 🩺 Acompanhamento de Vacinação Infantil -- AP3

**Universidade Federal de Ciências da Saúde de Porto Alegre (UFCSPA)**\
**Curso:** Informática Biomédica\
**Disciplina:** Tópicos Especiais em Informática Biomédica III\
**Autores:** Carlise Sebastiany e Matheus Castilhos\
**Semestre:** 2025/2

------------------------------------------------------------------------

## 🎯 Objetivo

Sistema acadêmico desenvolvido para registrar, acompanhar e analisar
vacinações infantis conforme o calendário vacinal oficial.\
Inclui CRUD completo, cálculo de atrasos e gráficos estatísticos.

------------------------------------------------------------------------

## ⚙️ Tecnologias

-   **PHP 8 ou superior**\
-   **MySQL 8 / MariaDB**\
-   **Composer + vlucas/phpdotenv**\
-   **HTML5 / CSS3 / JS + Chart.js**\
-   **Bootstrap Icons + Google Fonts (Inter)**

------------------------------------------------------------------------

## 🧱 Estrutura

    ap3/
    ├── assets/ → style.css
    ├── config/ → config.php / calendario_vacinal.php
    ├── others/ → AP3.pdf / vacinas.sql
    ├── src/
    │   ├── layout.php
    │   ├── functions.php
    │   └── routes/ (pacientes.php, vacinacoes.php, atrasos.php, estatisticas.php)
    ├── vendor/ (composer)
    ├── .env
    ├── composer.json
    └── index.php

------------------------------------------------------------------------

## 🧩 Instalação Passo a Passo (Windows 11)

### 1️⃣ Instalar o PHP manualmente

1.  Baixe o **PHP 8.x Non-Thread Safe (x64)** em
    [windows.php.net/download](https://windows.php.net/download/).\

2.  Extraia para `C:\php`.\

3.  Renomeie `php.ini-development` para **`php.ini`**.\

4.  Edite o arquivo e **ative** (remova o ponto-e-vírgula `;`) das
    linhas:

        extension_dir = "ext"
        extension=pdo_mysql
        extension=mysqli
        extension=openssl
        extension=curl

5.  Certifique-se de que o `openssl` esteja ativo --- ele é necessário
    para o Composer.\

6.  Adicione `C:\php` ao **PATH** do sistema:

    -   Painel de Controle → Sistema → Configurações Avançadas →
        Variáveis de Ambiente → Path → Novo → `C:\php`

Verifique no PowerShell:

``` bash
php -v
```

------------------------------------------------------------------------

### 2️⃣ Instalar o Composer globalmente

Baixe em [getcomposer.org/download](https://getcomposer.org/download/).\
Durante a instalação: - Escolha **Install for all users**;\
- Selecione `C:\php\php.exe` quando solicitado.

Teste:

``` bash
composer -V
```

------------------------------------------------------------------------

### 3️⃣ Instalar dependências do projeto

No terminal aberto dentro da pasta `ap3`:

``` bash
composer install
```

Se surgir erro de HTTPS ("Unable to find the wrapper https"), volte ao
passo 1 e verifique se o `openssl` está ativo no php.ini.

------------------------------------------------------------------------

### 4️⃣ Criar o banco de dados

1.  Abra o **MySQL Workbench**.\

2.  Execute o script:

    ``` sql
    SOURCE others/vacinas.sql;
    ```

3.  Certifique-se de que o banco foi criado e contém as tabelas
    `pacientes` e `vacinacoes`.

------------------------------------------------------------------------

### 5️⃣ Criar o arquivo `.env`

Crie um arquivo `.env` na raiz do projeto:

``` env
DB_HOST=127.0.0.1
DB_NAME=ap3_vacinas
DB_USER=root
DB_PASS=sua_senha_aqui
```

⚠️ **Nunca** envie o `.env` para o GitHub (adicione ao `.gitignore`).

------------------------------------------------------------------------

### 6️⃣ Executar o servidor PHP embutido

No terminal dentro da pasta `ap3`:

``` bash
php -S localhost:8080
```

Acesse: <http://localhost:8080>

------------------------------------------------------------------------

## 🩹 Funcionalidades

### 🧍 Pacientes

-   Cadastro e exclusão simples;\
-   Interface responsiva.

### 💉 Vacinações

-   Registro completo (paciente, vacina, equipe, endereço, posto);\
-   Exclusão individual;\
-   Layout moderno com campos uniformes.

### ⚠️ Atrasos

-   Calcula automaticamente conforme o calendário vacinal;\
-   Filtro: **Todos / Em dia / Com atraso**;\
-   Exibe detalhes de doses faltantes.

### 📊 Estatísticas

-   Gráficos interativos (Chart.js) por tipo de vacina e por equipe.

------------------------------------------------------------------------

## 🎨 Interface

Tema **azul-esverdeado**, foco em **clareza e usabilidade**, formulários
unificados com `.form-control` e botões responsivos.

------------------------------------------------------------------------

## 🧠 Lógica de Atrasos

1.  `config/calendario_vacinal.php` define `$calendario` com vacinas e
    idades máximas.\
2.  A função `calcular_atrasos_paciente()` compara idade × doses
    aplicadas.\
3.  Pacientes com doses pendentes recebem status "⚠️ Com atraso".

------------------------------------------------------------------------

## 🛠️ Erros Comuns e Soluções

  --------------------------------------------------------------------------------------------------------
  Erro                                          Causa Provável                      Solução
  --------------------------------------------- ----------------------------------- ----------------------
  `could not find driver`                       Extensão `pdo_mysql` desativada     Ativar no `php.ini`

  `Unable to find the wrapper "https"`          `openssl` desativado                Ativar
                                                                                    `extension=openssl`

  `Access denied for user 'ODBC'@'localhost'`   Login MySQL incorreto               Use `mysql -u root -p`
                                                                                    e confira senha

  Página em branco                              Erro de sintaxe PHP                 Rode
                                                                                    `php -l arquivo.php`

  `.env` não lido                               Falta da pasta `vendor/`            Execute
                                                                                    `composer install`
                                                                                    novamente
  --------------------------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 🔒 Boas Práticas

-   Uso de **PDO** com *prepared statements*;\
-   Saídas sanitizadas com `htmlspecialchars()`;\
-   Credenciais isoladas no `.env`;\
-   Estrutura de rotas clara e modular.

------------------------------------------------------------------------

## 👩‍💻 Autores

**Carlise Sebastiany**\
**Matheus Castilhos**

**Universidade Federal de Ciências da Saúde de Porto Alegre -- UFCSPA**\
**Curso:** Informática Biomédica \| 2025/2

------------------------------------------------------------------------

## 🧾 Licença

Uso acadêmico e educacional.\
Código aberto para fins de estudo e demonstração.
