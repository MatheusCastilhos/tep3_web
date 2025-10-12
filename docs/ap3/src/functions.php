<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/

// Autoload (Composer opcional)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

// Configuração de conexão (PDO)
require_once __DIR__ . '/../config/config.php';

function is_logged_in(): bool {
  return !empty($_SESSION['user'] ?? null);
}

function login_user(string $username): void {
  $_SESSION['user'] = $username;
}

function logout_user(): void {
  unset($_SESSION['user']);
}

function ensure_auth(): void {
  if (!is_logged_in()) {
    $current = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header("Location: index.php?p=login&redirect=" . urlencode($current));
    exit;
  }
}


global $calendario;
$calendario = [];

ob_start();
@include __DIR__ . '/../config/calendario_vacinal.php';
ob_end_clean();

// Aviso amigável caso o calendário não seja carregado
if (empty($calendario) && ($_GET['p'] ?? '') === 'atrasos') {
  echo '<div class="card" style="color:#b00;background:#fff3f3;padding:10px;border-radius:8px;">
          Calendário vacinal não encontrado ou incompatível. 
          Verifique <code>config/calendario_vacinal.php</code>.
        </div>';
}

if (!function_exists('idade_em_meses')) {
  function idade_em_meses(string $data_nasc, ?string $base = null): int {
    $n = new DateTime($data_nasc);
    $b = $base ? new DateTime($base) : new DateTime('now');
    $diff = $n->diff($b);
    return $diff->y * 12 + $diff->m + (int)($diff->d >= 15);
  }
}

function get_aplicacoes_por_paciente(PDO $pdo, int $paciente_id): array {
  $st = $pdo->prepare("
    SELECT vacina, data_aplicacao
    FROM vacinacoes
    WHERE paciente_id = ?
    ORDER BY data_aplicacao
  ");
  $st->execute([$paciente_id]);
  return $st->fetchAll();
}

function calcular_atrasos_paciente(array $calendario, array $aplicacoes, int $idade_meses): array {
  // Conta quantas doses já foram aplicadas por tipo de vacina
  $aplicadas = [];
  foreach ($aplicacoes as $a) {
    $nome = trim($a['vacina']);
    $aplicadas[$nome] = 1 + ($aplicadas[$nome] ?? 0);
  }

  $faltas = [];

  // Percorre cada vacina do calendário oficial
  foreach ($calendario as $vacina => $regras) {
    $deveriam = 0;
    foreach ($regras as $r) {
      if ($idade_meses >= $r['idade_max']) {
        $deveriam++;
      }
    }

    $feitas = $aplicadas[$vacina] ?? 0;

    // Registra se estiver em atraso
    if ($feitas < $deveriam) {
      $faltas[$vacina] = [
        'deveriam' => $deveriam,
        'feitas'   => $feitas,
        'faltam'   => $deveriam - $feitas,
      ];
    }
  }

  return $faltas;
}