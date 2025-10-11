<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
*/

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload; // usa Composer se existir
}
require_once __DIR__ . '/../config/config.php';

/* --- Importa o calendário vacinal do professor de forma silenciosa --- */
global $calendario; // mesmo nome usado no arquivo do professor
$calendario = [];   // inicializa

ob_start();
include __DIR__ . '/../config/calendario_vacinal.php';
ob_end_clean(); // limpa a saída do arquivo (que contém echos e prints)

/* Verifica se o calendário foi carregado corretamente */
if (empty($calendario) && (isset($_GET['p']) && $_GET['p'] === 'atrasos')) {
  echo '<div class="card" style="color:#b00;background:#fff3f3;padding:10px;border-radius:8px;">
        Calendário vacinal não encontrado ou incompatível. 
        Verifique <code>config/calendario_vacinal.php</code>.
        </div>';
}

/* --- Função de idade em meses (fallback caso o professor não tenha incluído) --- */
if (!function_exists('idade_em_meses')) {
  function idade_em_meses(string $data_nasc, ?string $base = null): int {
    $n = new DateTime($data_nasc);
    $b = $base ? new DateTime($base) : new DateTime('now');
    $diff = $n->diff($b);
    return $diff->y * 12 + $diff->m + (int)($diff->d >= 15);
  }
}

/* --- Busca todas as vacinações registradas de um paciente --- */
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

/* --- Compara doses esperadas (até a idade atual) com doses aplicadas --- */
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
    // Exemplo de estrutura: [ ["dose"=>1,"idade_max"=>2], ["dose"=>2,"idade_max"=>4], ... ]
    $deveriam = 0;
    foreach ($regras as $r) {
      if ($idade_meses >= $r['idade_max']) {
        $deveriam++; // já atingiu a idade para essa dose
      }
    }

    $feitas = $aplicadas[$vacina] ?? 0;

    // Se fez menos do que deveria, registra o atraso
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