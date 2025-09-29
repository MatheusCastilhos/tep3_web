// UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE - UFCSPA
// Nome: Matheus Castilhos
// Disciplina: Tópicos Especiais de Programação III
// Curso: Informática Biomédica
// Atividade Prática 2

// ==========================
// Referências de DOM
// ==========================
const btn = document.getElementById('btnGerar');
const statusEl = document.getElementById('status');
const alertArea = document.getElementById('alertArea');

// Views
const welcomeView = document.getElementById('welcomeView');
const dashboardView = document.getElementById('dashboardView');
const countryView = document.getElementById('countryView');

// Overlay de carregamento da view de país
const countryLoading = document.getElementById('countryLoading');

// Países (dashboard)
const tabelaPaisTB = document.getElementById('tabelaRanking').querySelector('tbody');
const pagPaisUL = document.getElementById('paginacaoPaises');
const paginaPaisInfo = document.getElementById('paginaInfo');
const badgePais = document.getElementById('badgeTotalPaises');
const selOrdenarPais = document.getElementById('ordenarPor');
const selDirecaoPais = document.getElementById('direcao');
const buscaPaisInput = document.getElementById('buscaPais');

// Línguas (dashboard)
const tabelaLingTB = document.getElementById('tabelaLingua').querySelector('tbody');
const pagLingUL = document.getElementById('paginacaoLinguas');
const paginaLingInfo = document.getElementById('paginaInfoLingua');
const badgeLing = document.getElementById('badgeTotalLinguas');
const selOrdenarLing = document.getElementById('ordenarPorLingua');
const selDirecaoLing = document.getElementById('direcaoLingua');

// Spinner
const spinBtn = document.getElementById('spinBtn');
const btnLabel = document.getElementById('btnLabel');

// View país
const btnBack = document.getElementById('btnBack');
const ctyFlag = document.getElementById('cty-flag');
const ctyTitle = document.getElementById('cty-title');
const ctySub = document.getElementById('cty-sub');
const ctyStats = document.getElementById('cty-stats');
const ctyStatus = document.getElementById('cty-status');

const chartHistoryCanvas = document.getElementById('chart-history');
const chartVaxCanvas = document.getElementById('chart-vax');
const chartPieCanvas = document.getElementById('chart-pie');

// ==========================
// Endpoints das APIs
// ==========================
const API_COVID_COUNTRIES = 'https://disease.sh/v3/covid-19/countries';
const API_VACCINE_LASTDAY = 'https://disease.sh/v3/covid-19/vaccine/coverage/countries?lastdays=1';
const API_RESTCOUNTRIES_ALL = 'https://restcountries.com/v3.1/all?fields=cca2,languages';
const API_RESTCOUNTRIES_BYA = (iso2) => `https://restcountries.com/v3.1/alpha/${iso2}`;
const API_COVID_CURRENT_BYI = (iso2) => `https://disease.sh/v3/covid-19/countries/${iso2}?strict=true&allowNull=true`;
const API_COVID_HIST_BYI = (iso2) => `https://disease.sh/v3/covid-19/historical/${iso2}?lastdays=all`;
const API_VAX_TIMELINE_BYI = (iso2) => `https://disease.sh/v3/covid-19/vaccine/coverage/countries/${iso2}?lastdays=all&fullData=false`;

// ==========================
// Estado global
// ==========================
let dadosCarregados  = []; 
let linguasAgregadas = [];

// Busca (países)
let termoBuscaPais = '';

// Paginação (países)
let pageSizePais = 20;
let pagePais = 1;
let totalPagPais = 1;
let _cachePaisOrdenado = [];

// Paginação (línguas)
let pageSizeLing = 20;
let pageLing = 1;
let totalPagLing = 1;
let _cacheLingOrdenada = [];

// Gráficos (view país)
let chartHistory = null;
let chartVax = null;
let chartPie = null;

// ==========================
// Utilitários de rede e UI
// ==========================
async function fetchJson(url, timeoutMs = 15000) {
  const ctrl = new AbortController();
  const id = setTimeout(() => ctrl.abort(), timeoutMs);
  try {
    const resp = await fetch(url, { signal: ctrl.signal });
    if (!resp.ok) throw new Error(`Erro ao buscar ${url}: ${resp.status}`);
    return await resp.json();
  } catch (e) {
    if (e.name === 'AbortError') throw new Error('Tempo de resposta excedido. Tente novamente.');
    throw e;
  } finally {
    clearTimeout(id);
  }
}

function showLoading(isLoading, msg = '') {
  if (!btn) return;
  if (isLoading) {
    btn.disabled = true;
    if (spinBtn)  spinBtn.classList.remove('d-none');
    if (btnLabel) btnLabel.textContent = 'Carregando...';
    if (statusEl) statusEl.textContent = msg || 'Buscando dados...';
  } else {
    btn.disabled = false;
    if (spinBtn)  spinBtn.classList.add('d-none');
    if (btnLabel) btnLabel.textContent = 'Gerar ranking';
    if (statusEl) statusEl.textContent = '';
  }
}

function showCountryLoading(show) {
  if (!countryLoading) return;
  countryLoading.style.display = show ? 'flex' : 'none';
}

function destroyChart(instance) {
  if (instance && typeof instance.destroy === 'function') {
    instance.destroy();
  }
}

function clearCountryView() {
  ctyFlag.removeAttribute('src');
  ctyFlag.alt = 'Bandeira';
  ctyTitle.textContent = 'Carregando...';
  ctySub.textContent = '';
  ctyStats.innerHTML = '';
  ctyStatus.textContent = '';
  destroyChart(chartHistory); chartHistory = null;
  destroyChart(chartVax);     chartVax = null;
  destroyChart(chartPie);     chartPie = null;
}

function clearAlerts() { alertArea.innerHTML = ''; }
function showAlert(type, message) {
  const div = document.createElement('div');
  div.className = `alert alert-${type} alert-dismissible fade show`;
  div.role = 'alert';
  div.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  `;
  alertArea.appendChild(div);
}

// Bandeiras (FlagCDN)
function flagUrl(iso2, size = '24x18') {
  if (!iso2) return '';
  return `https://flagcdn.com/${size}/${iso2.toLowerCase()}.png`;
}

// Debounce para busca
function debounce(fn, ms = 250) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), ms);
  };
}

// ==========================
function showView(id) {
  for (const el of [welcomeView, dashboardView, countryView]) {
    el.classList.add('d-none');
  }
  document.getElementById(id).classList.remove('d-none');
}

async function handleRoute() {
  const hash = location.hash || '#welcome';

  if (hash.startsWith('#country/')) {
    const iso2 = hash.split('/')[1]?.toUpperCase();
    if (iso2) {
      showView('countryView');
      clearCountryView();
      showCountryLoading(true);

      await new Promise(requestAnimationFrame);

      try {
        await loadCountryDetail(iso2);
      } finally {
        showCountryLoading(false);
      }
      return;
    }
  }

  if (hash === '#dashboard') {
    showView('dashboardView');
    return;
  }

  showView('welcomeView');
}

window.addEventListener('hashchange', handleRoute);

// ==========================
// Dados auxiliares (línguas, vacinas)
// ==========================
async function getLanguagesByIso2() {
  const data = await fetchJson(API_RESTCOUNTRIES_ALL);
  const map = new Map();
  for (const c of data) {
    const iso2 = (c.cca2 || '').toUpperCase();
    const langs = Object.values(c.languages || {}).filter(Boolean);
    if (iso2) map.set(iso2, langs);
  }
  return map;
}

async function getVaccinationsMap() {
  const data = await fetchJson(API_VACCINE_LASTDAY);
  const map = new Map();
  for (const item of data) {
    const valores = Object.values(item.timeline || item);
    const doses = valores.length ? valores[0] : 0;
    map.set(item.country, Number(doses) || 0);
  }
  return map;
}

// ==========================
// Carregar países + calcular %
// ==========================
async function carregarDados() {
  const [covid, vaccMap] = await Promise.all([
    fetchJson(API_COVID_COUNTRIES),
    getVaccinationsMap()
  ]);

  const rows = [];
  for (const c of covid) {
    const pais = c.country;
    const iso2 = (c.countryInfo && c.countryInfo.iso2) ? c.countryInfo.iso2.toUpperCase() : '';
    const populacao = Number(c.population) || 0;
    const mortes = Number(c.deaths) || 0;
    const doses = vaccMap.get(pais) || 0;
    if (!populacao || populacao <= 0) continue;

    const pctVac = (doses  / populacao) * 100;
    const pctMortes = (mortes / populacao) * 100;

    rows.push({ pais, iso2, populacao, doses, mortes, pctVac, pctMortes });
  }
  rows.sort((a, b) => b.pctVac - a.pctVac);
  return rows;
}

// ==========================
// Ordenação / Filtros
// ==========================
function ordenar(lista, chave, direcao = 'desc') {
  const mult = direcao === 'asc' ? 1 : -1;
  return [...lista].sort((a, b) => {
    const va = Number(a[chave]) || 0;
    const vb = Number(b[chave]) || 0;
    if (va < vb) return -1 * mult;
    if (va > vb) return  1 * mult;
    return 0;
  });
}
function filtrarValidos(lista) {
  return lista.filter(d =>
    Number.isFinite(d.pctVac) && Number.isFinite(d.pctMortes)
  );
}
function filtrarPorBusca(lista, termo) {
  const q = (termo || '').trim().toLowerCase();
  if (!q) return lista;
  return lista.filter(d => d.pais.toLowerCase().includes(q));
}

// ==========================
// Renderização TABELAS (dashboard)
// ==========================
function renderTabelaPaises(linhas) {
  tabelaPaisTB.innerHTML = '';
  const fmt2 = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });
  const fmt3 = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 3 });

  linhas.forEach((item, idx) => {
    const iso2 = item.iso2 || '';
    const src1x = flagUrl(iso2, '24x18');
    const src2x = flagUrl(iso2, '48x36');

    const paisBtn = iso2
      ? `<button class="btn btn-link p-0 country-link" data-cca2="${iso2}" data-name="${item.pais}" title="Detalhes de ${item.pais}">
           ${highlightMatch(item.pais, termoBuscaPais)}
         </button>`
      : highlightMatch(item.pais, termoBuscaPais);

    const flagImg = iso2 ? `
      <img
        src="${src1x}"
        srcset="${src1x} 1x, ${src2x} 2x"
        width="24" height="18"
        class="me-2 border rounded"
        alt="Bandeira de ${item.pais}"
        loading="lazy"
      />` : '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item._rank ?? idx + 1}</td>
      <td>${flagImg}${paisBtn}</td>
      <td class="text-end">${fmt2.format(item.pctVac)}</td>
      <td class="text-end">${fmt3.format(item.pctMortes)}</td>
    `;
    tabelaPaisTB.appendChild(tr);
  });
}

// realce do trecho buscado
function highlightMatch(text, query) {
  const q = (query || '').trim();
  if (!q) return text;
  const re = new RegExp(`(${escapeRegex(q)})`, 'ig');
  return text.replace(re, '<mark>$1</mark>');
}
function escapeRegex(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function renderTabelaLinguas(linhas) {
  tabelaLingTB.innerHTML = '';
  const fmt2 = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });
  const fmt3 = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 3 });
  const fmt0 = new Intl.NumberFormat('pt-BR');

  linhas.forEach((row, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${row._rank ?? idx + 1}</td>
      <td>${row.lingua}</td>
      <td class="text-end">${fmt2.format(row.pctVac)}</td>
      <td class="text-end">${fmt3.format(row.pctMortes)}</td>
      <td class="text-end">${fmt0.format(row.populacao)}</td>
      <td class="text-end">${row.paisesCount}</td>
    `;
    tabelaLingTB.appendChild(tr);
  });
}

// ==========================
// Paginação genérica
// ==========================
function buildPagination(ulEl, total, current) {
  ulEl.innerHTML = '';
  if (total <= 1) return;

  const windowSize = 7;
  const makeItem = (label, page, disabled = false, active = false) => `
    <li class="page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}">
      <a class="page-link" href="#" data-page="${page}">${label}</a>
    </li>`;

  let html = '';
  html += makeItem('«', Math.max(1, current - 1), current === 1);

  let start = Math.max(1, current - Math.floor(windowSize / 2));
  let end = Math.min(total, start + windowSize - 1);
  if (end - start + 1 < windowSize) start = Math.max(1, end - windowSize + 1);

  for (let p = start; p <= end; p++) html += makeItem(String(p), p, false, p === current);
  html += makeItem('»', Math.min(total, current + 1), current === total);

  ulEl.innerHTML = html;
}

function paginate(list, page, size) {
  const start = (page - 1) * size;
  return list.slice(start, start + size).map((row, i) => ({ ...row, _rank: start + i + 1 }));
}

// Países (com busca)
function renderPaginadoPaises(listaOrdenadaTotal, resetPage = false) {
  const filtrados = filtrarPorBusca(listaOrdenadaTotal, termoBuscaPais);
  _cachePaisOrdenado = filtrados;

  totalPagPais = Math.max(1, Math.ceil(filtrados.length / pageSizePais));
  if (resetPage) pagePais = 1;
  pagePais = Math.min(Math.max(1, pagePais), totalPagPais);

  const start = (pagePais - 1) * pageSizePais;
  const end = Math.min(start + pageSizePais, filtrados.length);
  const pageRows = filtrados.slice(start, end).map((row, i) => ({ ...row, _rank: start + i + 1 }));

  renderTabelaPaises(pageRows);
  buildPagination(pagPaisUL, totalPagPais, pagePais);

  paginaPaisInfo.textContent = `Mostrando ${filtrados.length ? start + 1 : 0}–${end} de ${filtrados.length}`;
  badgePais.textContent = `${filtrados.length} países`;
  badgePais.classList.toggle('d-none', filtrados.length === 0);
}

// Línguas
function renderPaginadoLinguas(listaOrdenada, resetPage = false) {
  _cacheLingOrdenada = listaOrdenada;
  totalPagLing = Math.max(1, Math.ceil(listaOrdenada.length / pageSizeLing));
  if (resetPage) pageLing = 1;
  pageLing = Math.min(Math.max(1, pageLing), totalPagLing);

  const start = (pageLing - 1) * pageSizeLing;
  const end = Math.min(start + pageSizeLing, listaOrdenada.length);
  const pageRows = listaOrdenada.slice(start, end).map((row, i) => ({ ...row, _rank: start + i + 1 }));

  renderTabelaLinguas(pageRows);
  buildPagination(pagLingUL, totalPagLing, pageLing);

  paginaLingInfo.textContent = `Mostrando ${listaOrdenada.length ? start + 1 : 0}–${end} de ${listaOrdenada.length}`;
  badgeLing.textContent = `${listaOrdenada.length} línguas`;
  badgeLing.classList.toggle('d-none', listaOrdenada.length === 0);
}

// Delegação de eventos das paginações
pagPaisUL.addEventListener('click', (e) => {
  const a = e.target.closest('a.page-link'); if (!a) return; e.preventDefault();
  const p = Number(a.dataset.page); if (!Number.isFinite(p)) return;
  if (p < 1 || p > totalPagPais || p === pagePais) return;
  pagePais = p;
  renderPaginadoPaises(_cachePaisOrdenado);
});
pagLingUL.addEventListener('click', (e) => {
  const a = e.target.closest('a.page-link'); if (!a) return; e.preventDefault();
  const p = Number(a.dataset.page); if (!Number.isFinite(p)) return;
  if (p < 1 || p > totalPagLing || p === pageLing) return;
  pageLing = p;
  renderPaginadoLinguas(_cacheLingOrdenada);
});

// ==========================
// Atualizar UI (países / línguas) - dashboard
// ==========================
function atualizarUIPaises(resetPage = false) {
  const ordenarPor = selOrdenarPais.value; 
  const direcao = selDirecaoPais.value; 

  const validos = filtrarValidos(dadosCarregados);
  const ordenados = ordenar(validos, ordenarPor, direcao);

  renderPaginadoPaises(ordenados, resetPage);
}

function ordenarListaLingua(lista, chave, direcao = 'desc') {
  const mult = direcao === 'asc' ? 1 : -1;
  return [...lista].sort((a, b) => {
    const va = Number(a[chave]) || 0;
    const vb = Number(b[chave]) || 0;
    if (va < vb) return -1 * mult;
    if (va > vb) return  1 * mult;
    return 0;
  });
}

async function agregarPorLingua(dados) {
  const langMap = await getLanguagesByIso2();
  const acc = new Map(); 

  for (const item of dados) {
    if (!item.iso2) continue;
    const langs = langMap.get(item.iso2) || [];
    for (const L of langs) {
      if (!acc.has(L)) acc.set(L, { lingua: L, doses: 0, mortes: 0, populacao: 0, paises: new Set() });
      const ref = acc.get(L);
      ref.doses += item.doses;
      ref.mortes += item.mortes;
      ref.populacao += item.populacao;
      ref.paises.add(item.pais);
    }
  }

  const out = [];
  for (const obj of acc.values()) {
    const pctVac = obj.populacao ? (obj.doses  / obj.populacao) * 100 : 0;
    const pctMortes = obj.populacao ? (obj.mortes / obj.populacao) * 100 : 0;
    out.push({ lingua: obj.lingua, pctVac, pctMortes, populacao: obj.populacao, paisesCount: obj.paises.size });
  }
  return out;
}

async function atualizarUILinguas(resetPage = false) {
  const ordenarPor = selOrdenarLing.value; 
  const direcao = selDirecaoLing.value;

  linguasAgregadas = await agregarPorLingua(dadosCarregados);
  const ordenadas = ordenarListaLingua(linguasAgregadas, ordenarPor, direcao);

  renderPaginadoLinguas(ordenadas, resetPage);
}

// ==========================
// View País — helpers de dados/gráficos
// ==========================
function renderStatCards(stats) {
  const fmt0 = new Intl.NumberFormat('pt-BR');
  const items = [
    { key: 'cases', label: 'Casos' },
    { key: 'deaths', label: 'Óbitos' },
    { key: 'active', label: 'Ativos' },
    { key: 'recovered', label: 'Recuperados' },
    { key: 'tests', label: 'Testes' },
    { key: 'population', label: 'População' },
  ];
  ctyStats.innerHTML = items.map(({key, label}) => `
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body">
          <div class="text-muted small">${label}</div>
          <div class="h5 mb-0">${fmt0.format(stats[key] ?? 0)}</div>
        </div>
      </div>
    </div>
  `).join('');
}

function buildDailySeries(timeline) {
  const cases = timeline?.cases || {};
  const deaths = timeline?.deaths || {};
  const dates = Object.keys(cases);
  const dailyCases = [];
  const dailyDeaths = [];
  for (let i = 0; i < dates.length; i++) {
    const d = dates[i];
    const prev = dates[i - 1];
    const c = cases[d] ?? 0;
    const p = prev ? (cases[prev] ?? 0) : 0;
    dailyCases.push(Math.max(0, c - p));
    const cd = deaths[d] ?? 0;
    const pd = prev ? (deaths[prev] ?? 0) : 0;
    dailyDeaths.push(Math.max(0, cd - pd));
  }
  return { dates, dailyCases, dailyDeaths };
}

function buildVaxSeries(vaxTimeline) {
  const entries = vaxTimeline && typeof vaxTimeline === 'object' ? vaxTimeline : {};
  const vaxDates = Object.keys(entries);
  const vaxValues = vaxDates.map(d => entries[d] ?? null);
  return { vaxDates, vaxValues };
}

function upsertLineChart(instance, canvas, data, options = {}) {
  if (instance) instance.destroy();
  const ctx = canvas.getContext('2d');
  return new Chart(ctx, {
    type: 'line',
    data,
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      scales: { x: { ticks: { maxTicksLimit: 6 } }, y: { beginAtZero: true } },
      plugins: { legend: { display: true } }
    }, options)
  });
}

function upsertPieChart(instance, canvas, { labels, data }) {
  if (instance) instance.destroy();
  const ctx = canvas.getContext('2d');
  return new Chart(ctx, {
    type: 'pie',
    data: {
      labels,
      datasets: [{ data }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
}

// ==========================
// View País — carregamento
// ==========================
async function loadCountryDetail(iso2) {
  ctyStatus.textContent = '';

  try {
    // 1) Metadados do país
    const rc = await fetchJson(API_RESTCOUNTRIES_BYA(iso2));
    const info = rc?.[0];
    if (!info) throw new Error('País não encontrado.');
    const name = info.name?.common || '—';
    const flagPng = info.flags?.png || info.flags?.svg;
    const region = info.region || '—';
    const population = info.population || 0;
    const languages = info.languages ? Object.values(info.languages).join(', ') : '—';

    ctyTitle.textContent = name;
    ctySub.textContent = `${region} • Pop. ${population.toLocaleString('pt-BR')} • ${languages}`;
    ctyFlag.src = flagPng;
    ctyFlag.alt = `Bandeira de ${name}`;

    // 2) Estado atual
    const current = await fetchJson(API_COVID_CURRENT_BYI(iso2));

    renderStatCards({
      cases: current?.cases,
      deaths: current?.deaths,
      active: current?.active,
      recovered: current?.recovered,
      tests: current?.tests,
      population: current?.population ?? population
    });

    // 3) Histórico (casos/óbitos)
    const hist = await fetchJson(API_COVID_HIST_BYI(iso2));
    const timeline = hist?.timeline || hist;
    const { dates, dailyCases, dailyDeaths } = buildDailySeries(timeline);

    chartHistory = upsertLineChart(chartHistory, chartHistoryCanvas, {
      labels: dates,
      datasets: [
        { label: 'Casos/dia', data: dailyCases },
        { label: 'Óbitos/dia', data: dailyDeaths }
      ]
    });

    // 4) Vacinação ao longo do tempo
    const vax = await fetchJson(API_VAX_TIMELINE_BYI(iso2));
    const vaxTimeline = vax?.timeline || vax;
    const { vaxDates, vaxValues } = buildVaxSeries(vaxTimeline);

    chartVax = upsertLineChart(chartVax, chartVaxCanvas, {
      labels: vaxDates,
      datasets: [{ label: 'Doses acumuladas', data: vaxValues }]
    });

    // 5) Pizza situação atual
    const active = current?.active ?? 0;
    const recovered = current?.recovered ?? 0;
    const deaths = current?.deaths ?? 0;

    chartPie = upsertPieChart(chartPie, chartPieCanvas, {
      labels: ['Ativos', 'Recuperados', 'Óbitos'],
      data: [active, recovered, deaths]
    });

    ctyStatus.textContent = `Atualizado agora • Fonte: disease.sh + RestCountries`;
  } catch (err) {
    console.error(err);
    ctyStatus.textContent = 'Não foi possível carregar os dados do país.';
    showAlert('danger', `<strong>Ops!</strong> Falha ao carregar país. ${err.message || ''}`);
  }
}

// ==========================
// Listeners principais
// ==========================
btn?.addEventListener('click', async () => {
  clearAlerts();
  showLoading(true, 'Buscando dados...');
  try {
    dadosCarregados = await carregarDados();

    // Países
    atualizarUIPaises(true);

    // Línguas
    await atualizarUILinguas(true);

    statusEl.textContent = `Carregado: ${dadosCarregados.length} países, ${linguasAgregadas.length} línguas.`;
    showAlert('success', 'Dados carregados com sucesso!');

    // Navega para o dashboard
    location.hash = '#dashboard';
  } catch (err) {
    console.error(err);
    showAlert('danger', `<strong>Ops!</strong> Não foi possível carregar os dados.<br>Detalhes: ${err.message || 'Erro desconhecido.'}`);
    statusEl.textContent = 'Erro ao carregar dados.';
    location.hash = '#welcome';
  } finally {
    showLoading(false);
  }
});

// Controles nas tabelas (dashboard)
selOrdenarPais.addEventListener('change', () => atualizarUIPaises(true));
selDirecaoPais.addEventListener('change', () => atualizarUIPaises(true));

// Busca por país (com debounce)
const onSearchPais = debounce((e) => {
  termoBuscaPais = e.target.value || '';
  renderPaginadoPaises(ordenar(filtrarValidos(dadosCarregados), selOrdenarPais.value, selDirecaoPais.value), true);
}, 200);
buscaPaisInput.addEventListener('input', onSearchPais);

// Línguas
selOrdenarLing.addEventListener('change', () => atualizarUILinguas(true));
selDirecaoLing.addEventListener('change', () => atualizarUILinguas(true));

// Clique em país (delegação): abre view de país
document.addEventListener('click', (ev) => {
  const btn = ev.target.closest('.country-link');
  if (!btn) return;
  const cca2 = btn.dataset.cca2;
  if (cca2) location.hash = `#country/${cca2}`;
});

// Botão voltar na view de país
btnBack.addEventListener('click', () => {
  location.hash = '#dashboard';
});

// Inicializa rota atual ao carregar
handleRoute();