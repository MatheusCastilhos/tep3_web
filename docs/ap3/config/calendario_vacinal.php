<?php
// Calendário vacinal (conforme regras fornecidas)

$calendario = [
    "BCG" => [["dose" => 1, "idade_max" => 60]], // Significado: As vacinas do BCG possuem 1 única dose e devem ser realizadas até o mês 60 de idade (5 anos)
    "Hepatite B" => [ // Significado: A Hepatite B possui 3 doses, a primeira deverá ser feita até o mês 1, o segundo no mês 2 e o terceiro até 6 meses de vida
        ["dose" => 1, "idade_max" => 1], 
        ["dose" => 2, "idade_max" => 2],
        ["dose" => 3, "idade_max" => 6],
    ],
    "Pentavalente" => [
        ["dose" => 1, "idade_max" => 2],
        ["dose" => 2, "idade_max" => 4],
        ["dose" => 3, "idade_max" => 6],
    ],
    "Poliomielite" => [
        ["dose" => 1, "idade_max" => 2],
        ["dose" => 2, "idade_max" => 4],
        ["dose" => 3, "idade_max" => 6],
    ],
    "Tríplice Viral" => [
        ["dose" => 1, "idade_max" => 12],
        ["dose" => 2, "idade_max" => 15],
    ],
    "Rotavírus" => [
        ["dose" => 1, "idade_max" => 2],
        ["dose" => 2, "idade_max" => 4],
    ],
    "Influenza" => [["dose" => 1, "idade_max" => 999]],
    "COVID-19" => [
        ["dose" => 1, "idade_max" => 24],
        ["dose" => 2, "idade_max" => 36],
    ]
];

// Converter data de nascimento para meses de vida
function idadeEmMeses($dataNasc) {
    $dn = new DateTime($dataNasc);
    $hoje = new DateTime();
    $diff = $dn->diff($hoje);
    return $diff->y * 12 + $diff->m;
}

// Exemplo de uso da função
echo "A data de nascimento 09/01/2024 possui " . idadeEmMeses("2024-01-09") . " meses de vida.<br><br><hr>";

// Array com o nome das vacinas de $calendario
$nomesVacinas = array_keys($calendario); // Crie um array com o nome das vacinas ["BCG", "Hepatite B"]

for ($i = 0; $i < count($calendario); $i++) { // Percorre a quantidade de nomes de vacina no $calendario
    $nomeVacina = $nomesVacinas[$i];
    echo "<b>$nomeVacina</b><br>"; // Mostra o nome da vacina
    
    foreach ($calendario[$nomeVacina] as $regra) { // Para cada vacina existente, mostra a dose e a idade máxima
        echo "- Dose " . $regra['dose'] . " até " . $regra['idade_max'] . " meses<br>";
    }
    echo "<hr>";
}

// Dica extra
$dosesRecebidas = 0; // exemplo fixo, mas na prática viria do banco de dados. Nesse caso está considerando que o paciente tomou 0 doses de todas as vacinas
$idade = 12; // em meses - 1 ano

for ($i = 0; $i < count($calendario); $i++) {
    $nomeVacina = $nomesVacinas[$i]; // pega o nome pelo índice
    foreach ($calendario[$nomeVacina] as $regra) {
        if ($dosesRecebidas < $regra['dose'] && $idade > $regra['idade_max']) {
            echo "Paciente atrasado na dose {$regra['dose']} de {$nomeVacina}<br>";
        }
    }
}



?>
