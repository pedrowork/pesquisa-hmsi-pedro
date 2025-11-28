<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatisfacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satisfacoes = [
            ['cod' => 0, 'descricao' => 'NA', 'cod_tipo_pergunta' => 1],
            ['cod' => 1, 'descricao' => 'RUIM', 'cod_tipo_pergunta' => 1],
            ['cod' => 2, 'descricao' => 'REGULAR', 'cod_tipo_pergunta' => 1],
            ['cod' => 3, 'descricao' => 'BOM', 'cod_tipo_pergunta' => 1],
            ['cod' => 4, 'descricao' => 'ÓTIMO', 'cod_tipo_pergunta' => 1],
            ['cod' => 5, 'descricao' => 'SIM', 'cod_tipo_pergunta' => 2],
            ['cod' => 6, 'descricao' => 'NÃO', 'cod_tipo_pergunta' => 2],
            ['cod' => 7, 'descricao' => 'NA', 'cod_tipo_pergunta' => 3],
            ['cod' => 9, 'descricao' => 'Observação', 'cod_tipo_pergunta' => 4],
            ['cod' => 10, 'descricao' => '0', 'cod_tipo_pergunta' => 3],
            ['cod' => 11, 'descricao' => '1', 'cod_tipo_pergunta' => 3],
            ['cod' => 12, 'descricao' => '2', 'cod_tipo_pergunta' => 3],
            ['cod' => 13, 'descricao' => '3', 'cod_tipo_pergunta' => 3],
            ['cod' => 14, 'descricao' => '4', 'cod_tipo_pergunta' => 3],
            ['cod' => 15, 'descricao' => '5', 'cod_tipo_pergunta' => 3],
            ['cod' => 16, 'descricao' => '6', 'cod_tipo_pergunta' => 3],
            ['cod' => 17, 'descricao' => '7', 'cod_tipo_pergunta' => 3],
            ['cod' => 18, 'descricao' => '8', 'cod_tipo_pergunta' => 3],
            ['cod' => 19, 'descricao' => '9', 'cod_tipo_pergunta' => 3],
            ['cod' => 20, 'descricao' => '10', 'cod_tipo_pergunta' => 3],
        ];

        foreach ($satisfacoes as $satisfacao) {
            DB::table('satisfacao')->updateOrInsert(
                ['cod' => $satisfacao['cod']],
                [
                    'descricao' => $satisfacao['descricao'],
                    'cod_tipo_pergunta' => $satisfacao['cod_tipo_pergunta'],
                ]
            );
        }

        $this->command->info('Satisfações criadas/atualizadas: ' . count($satisfacoes) . ' registros');
    }
}


