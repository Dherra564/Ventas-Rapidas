<?php

trait ComparadorTexto
{
    public function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), "UTF-8");
        $texto = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $texto);
        $texto = preg_replace("/[^a-z0-9\s]/", " ", $texto);
        $texto = preg_replace("/\s+/", " ", $texto);

        return trim($texto);
    }

    public function calcularSimilitud(string $textoA, string $textoB): float
    {
        $normA = $this->normalizarTexto($textoA);
        $normB = $this->normalizarTexto($textoB);

        if ($normA === $normB) {
            return 100.0;
        }

        similar_text($normA, $normB, $porcentaje);

        return round($porcentaje, 1);
    }

    public function ordenarPorSimilitud(array $candidatos, string $nombreBuscado, callable $obtenerNombre, float $umbralMinimo = 70.0): array
    {
        $resultados = [];

        foreach ($candidatos as $candidato) {
            $nombreCandidato = $obtenerNombre($candidato);
            $similitud = $this->calcularSimilitud($nombreBuscado, $nombreCandidato);

            if ($similitud >= $umbralMinimo) {
                $resultados[] = ["similitud" => $similitud, "dato" => $candidato];
            }
        }

        usort($resultados, fn($a, $b) => $b["similitud"] <=> $a["similitud"]);

        return $resultados;
    }
}