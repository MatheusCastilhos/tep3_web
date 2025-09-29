# Painel COVID‑19 (Ranking por País e por Língua)

Aplicação web de página única (SPA) que consome **duas APIs públicas** para exibir **rankings e detalhes** de COVID‑19 por **país** e por **língua**, com **gráficos** (séries históricas e situação atual), **busca**, **ordenação** e **paginação**. Desenvolvido para a disciplina **Tópicos Especiais de Programação III** (UFCSPA).

---

## 🧭 Sumário
- [Visão Geral](#-visão-geral)
- [Como Executar](#-como-executar)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Arquivos do Projeto](#-arquivos-do-projeto)
- [Arquitetura e Fluxo](#-arquitetura-e-fluxo)
- [Dados e Limitações](#-dados-e-limitações)
- [Acessibilidade e UX](#-acessibilidade-e-ux)
- [Notas sobre Vacinação > 100%](#-notas-sobre-vacinação--100)
- [Solução de Problemas](#-solução-de-problemas)
- [Licença e Créditos](#-licença-e-créditos)

---

## 🔎 Visão Geral
O painel apresenta dois recortes principais:
1. **Ranking por País** — indicadores atuais e acesso à página detalhada de cada país (com gráficos de casos/óbitos e vacinação quando disponível).
2. **Ranking por Língua** — agregação por idioma principal informado pelo REST Countries, permitindo observar tendências por grupos linguísticos.

A experiência é **SPA**: a navegação entre lista e detalhe ocorre **sem recarregar a página**, com **overlay de carregamento** enquanto os dados são buscados.

---

## ▶ Como Executar
1. Baixe/clon e os arquivos e mantenha-os na **mesma pasta**.
2. Abra o arquivo **`index.html`** diretamente no navegador.
3. Na **tela inicial**, leia o resumo e clique em **“Gerar ranking”**.
4. Use **busca**, **ordenação** e **paginação** para explorar.
5. **Clique em um país** para abrir a visão detalhada (um overlay indica o carregamento).

> Requisitos: conexão com a internet (as APIs são remotas). Não é necessário servidor local.

---

## ✨ Funcionalidades
- **Tela de boas‑vindas** com resumo do projeto, limitações e nota sobre a métrica de vacinação.
- **Ranking por País**: busca (com _debounce_ e highlight), ordenação configurável e paginação com janela.
- **Ranking por Língua**: agregação por idioma principal.
- **Detalhe do País** (SPA com hash‑routing): indicadores atuais e **gráficos** com **Chart.js**:
  - Série histórica de **casos** e **óbitos**.
  - Série histórica de **vacinação** (se disponível).
  - Gráfico de **pizza** (ativos/recuperados/óbitos), quando aplicável.
- **Estados e erros tratados**: mensagens amigáveis, indicadores de carregamento, queda controlada de componentes quando a fonte não fornece dados.
- **Higiene técnica**: destruição de gráficos antigos, timeouts de rede com `AbortController`, _debounce_ na busca, paginação eficiente.

---

## 🧰 Tecnologias
- **HTML5** + **CSS** com **Bootstrap 5** (via CDN)
- **JavaScript** (ES6+)
- **Chart.js** (linhas e pizza)
- **APIs públicas**:
  - COVID‑19: https://disease.sh/
  - Países: https://restcountries.com/

---

## 📁 Arquivos do Projeto
- **`index.html`** — estrutura visual (Bootstrap via CDN), contêineres de views, cabeçalho com assinatura, linha divisória persistente e elementos de estado (alerts/overlays).
- **`script.js`** — lógica da SPA (roteamento por hash), integração com APIs, agregação por língua, ordenação, paginação, busca com _debounce_, renderização de gráficos e tratamento de erros.
- **`AP2.pdf`** — enunciado/briefing da atividade (para referência).

---

## 🧠 Arquitetura e Fluxo
- **Roteamento por hash** (`#home`, `#country=ISO`): permite trocar de view sem recarregar a página.
- **Camada de dados**:
  - `fetch` com **timeout** (via `AbortController`) e tratamento de exceções.
  - Combinação/normalização entre disease.sh e REST Countries.
- **Camada de apresentação**:
  - Renderização de listas (país e língua) com **busca, ordenação e paginação**.
  - Detalhe do país com **overlay** enquanto séries e indicadores são buscados.
  - **Chart.js** para séries e pizza; **destruição** de instâncias ao trocar de país para evitar erro “canvas already in use”.
- **UX/A11y**: estado de carregamento via `aria-busy/aria-live`, alerts acessíveis, foco visível, feedback textual.

---

## 🧪 Dados e Limitações
- Nem todos os países possuem **séries completas** (sobretudo vacinação). Nesses casos, os gráficos inexistentes **não são renderizados** e a interface informa o usuário.
- As APIs são **dinâmicas** e podem:
  - mudar o **esquema** de resposta;
  - aplicar **limites de taxa**;
  - apresentar **intermitências** temporárias.
- A agregação por **língua** usa o **idioma principal** reportado; países multilíngues podem ser **simplificados**.
- O painel **não persiste** dados localmente (sem cache offline).

---

## ♿ Acessibilidade e UX
- **Alerts** com linguagem clara e foco/visibilidade apropriados.
- **Overlay de carregamento** com `aria-busy` e `aria-live` em pontos críticos.
- **Controles** de busca/ordenação/paginação com feedback textual e tamanho/alinhamento adequados.
- **Layout responsivo** (Bootstrap) para bom uso em telas pequenas e grandes.

---

## 📝 Notas sobre Vacinação > 100%
Valores acima de **100%** podem aparecer quando:
- O **numerador** (ex.: doses administradas, pessoas vacinadas em um recorte específico) e o **denominador** (população) **não** são diretamente comparáveis ou estão em **momentos distintos**.
- Há **campanhas** envolvendo **não residentes** ou **doses de reforço** múltiplas.
O painel **explicita** essa observação na tela inicial para evitar interpretações errôneas.

---

## 🛠️ Solução de Problemas
- **“Não foi possível carregar os dados”**  
  Verifique a conexão e tente novamente. As APIs podem estar intermitentes.
- **Gráfico não aparece**  
  Em geral indica **falta de série histórica** para aquele indicador/país. O restante da página funciona normalmente.
- **Navegação travando**  
  Feche abas antigas do painel e recarregue. O código destrói instâncias do Chart.js ao trocar de país; recargas parciais do navegador podem interromper esse ciclo.
- **APIs bloqueadas por CORS/empresa**  
  Alguns ambientes corporativos bloqueiam domínios de APIs públicas. Tente em outra rede/dispositivo.

---

## 📜 Licença e Créditos
- **Uso acadêmico/educacional**. Consulte os **termos** das APIs de terceiros antes de uso em produção.
- **Dados**: disease.sh (COVID‑19) e REST Countries (países).
- **UI**: Bootstrap 5. **Gráficos**: Chart.js.
- **Autor**: Matheus Castilhos — UFCSPA.

