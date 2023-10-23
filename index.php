<?php
$arquivo = 'log.txt';

if(!file_exists($arquivo)){
    die('Arquivo de log não encontrado.');
}

$log = file_get_contents($arquivo);
$log = explode("\n", $log);
array_shift($log); 

$pilotos = [];
$melhor_volta = [
    'tempo' => '1000:00.000',
    'piloto' => ''
];

foreach ($log as $linha) {
    $dados = explode(' ', $linha);

    $codigo = $dados[1];
    $posicao = array_search($codigo, array_column($pilotos, 'codigo'));

    if ($posicao !== false) {
        
        $pilotos[$posicao]['voltas']++;
        
        $pilotos[$posicao]['tempo'] = somarTempos($pilotos[$posicao]['tempo'], $dados[5]);

        if(converterTempoParaSegundos($dados[5]) < converterTempoParaSegundos($pilotos[$posicao]['melhor'])){
            $pilotos[$posicao]['melhor'] = $dados[5];
        }

    } else {
        $pilotos[] = [
            'codigo' => $dados[1],
            'nome' => $dados[3],
            'voltas' => (int)$dados[4],
            'tempo' => $dados[5],
            'melhor' => $dados[5],
        ];
    }

    if(converterTempoParaSegundos($dados[5]) < converterTempoParaSegundos($melhor_volta['tempo'])){
        $melhor_volta['tempo'] = $dados[5];
        $melhor_volta['piloto'] = $dados[3];
    }
}

$pilotos = ordenarPilotosPeloTempo($pilotos);

$i = 1;

$html = '<table border="1">';
$html .= '<tr><th>Posição</th><th>Código Piloto</th><th>Nome Piloto</th><th>Voltas</th><th>Tempo Total</th><th>Melhor Volta</th></tr>';
foreach ($pilotos as $piloto) {
    $html .= '<tr>';
    $html .= '<td>' . $i++ . '</td>';
    $html .= '<td>' . $piloto['codigo'] . '</td>';
    $html .= '<td>' . $piloto['nome'] . '</td>';
    $html .= '<td>' . $piloto['voltas'] . '</td>';
    $html .= '<td>' . $piloto['tempo'] . '</td>';
    $html .= '<td>' . $piloto['melhor'] . '</td>';
    $html .= '</tr>';
}
$html .= '</table>';

echo $html;

echo "<p>A melhor volta da corrida foi {$melhor_volta['tempo']} do piloto {$melhor_volta['piloto']}</p>";

function somarTempos(string $tempo1, string $tempo2): string {
    [$minutos1, $segundos1, $milissegundos1] = preg_split("/[:.]/", $tempo1);
    [$minutos2, $segundos2, $milissegundos2] = preg_split("/[:.]/", $tempo2);

    $totalMinutos = $minutos1 + $minutos2;
    $totalSegundos = $segundos1 + $segundos2;
    $totalMilissegundos = $milissegundos1 + $milissegundos2;

    $totalSegundos += (int)($totalMilissegundos / 1000);
    $totalMilissegundos = $totalMilissegundos % 1000;

    $totalMinutos += (int)($totalSegundos / 60);
    $totalSegundos = $totalSegundos % 60;

    return sprintf('%02d:%02d.%03d', $totalMinutos, $totalSegundos, $totalMilissegundos);
}

function converterTempoParaSegundos(string $tempo): float {
    [$minutos, $segundos, $milissegundos] = preg_split("/[:.]/", $tempo);
    return (float)$minutos * 60 + (float)$segundos + (float)$milissegundos / 1000;
}

function ordenarPilotosPeloTempo($pilotos) {
    usort($pilotos, function ($a, $b) {
        $tempoA = converterTempoParaSegundos($a['tempo']);
        $tempoB = converterTempoParaSegundos($b['tempo']);
        return $tempoA - $tempoB;
    });
    
    return $pilotos;
}
