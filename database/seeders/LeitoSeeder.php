<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leitos = [];

        // Utin Amor Perfeito (5) - LEITO 01 a LEITO 20
        for ($i = 1; $i <= 20; $i++) {
            $leitos[] = [
                'cod' => $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 5,
            ];
        }

        // Ala Girassol (7) - LEITO 01 a LEITO 30
        for ($i = 1; $i <= 30; $i++) {
            $leitos[] = [
                'cod' => 20 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 7,
            ];
        }

        // Flor de Lis (1) - LEITO 01 a LEITO 25
        for ($i = 1; $i <= 25; $i++) {
            $leitos[] = [
                'cod' => 50 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 1,
            ];
        }

        // Flor de Cerejeira (2) - LEITO 01 a LEITO 28
        for ($i = 1; $i <= 28; $i++) {
            $leitos[] = [
                'cod' => 75 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 2,
            ];
        }

        // Lírios (3) - LEITO 01 a LEITO 16
        for ($i = 1; $i <= 16; $i++) {
            $leitos[] = [
                'cod' => 103 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 3,
            ];
        }

        // Flor de Laranjeira (4) - LEITO 01 a LEITO 31
        for ($i = 1; $i <= 31; $i++) {
            $leitos[] = [
                'cod' => 119 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 4,
            ];
        }

        // UCinco (6) - LEITO 01 a LEITO 30
        for ($i = 1; $i <= 30; $i++) {
            $leitos[] = [
                'cod' => 150 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 6,
            ];
        }

        // Alfazema (8) - LEITO 01 a LEITO 04
        for ($i = 1; $i <= 4; $i++) {
            $leitos[] = [
                'cod' => 180 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 8,
            ];
        }

        // Jasmin (9) - LEITO 01 a LEITO 10
        for ($i = 1; $i <= 10; $i++) {
            $leitos[] = [
                'cod' => 184 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 9,
            ];
        }

        // Violeta (14) - LEITO 01 a LEITO 25
        for ($i = 1; $i <= 25; $i++) {
            $leitos[] = [
                'cod' => 194 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 14,
            ];
        }

        // Rosa (15) - LEITO 01 a LEITO 10
        for ($i = 1; $i <= 10; $i++) {
            $leitos[] = [
                'cod' => 219 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 15,
            ];
        }

        // Cravo (16) - LEITO 01 a LEITO 03
        for ($i = 1; $i <= 3; $i++) {
            $leitos[] = [
                'cod' => 229 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 16,
            ];
        }

        // Margarida (17) - LEITO 01 a LEITO 26
        for ($i = 1; $i <= 26; $i++) {
            $leitos[] = [
                'cod' => 232 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 17,
            ];
        }

        // Lótus (18) - LEITO 01 a LEITO 20
        for ($i = 1; $i <= 20; $i++) {
            $leitos[] = [
                'cod' => 258 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 18,
            ];
        }

        // UTI Hortênsia (19) - LEITO 01 a LEITO 10
        for ($i = 1; $i <= 10; $i++) {
            $leitos[] = [
                'cod' => 278 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 19,
            ];
        }

        // UTI Orquídea (20) - LEITO 01 a LEITO 11
        for ($i = 1; $i <= 11; $i++) {
            $leitos[] = [
                'cod' => 288 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 20,
            ];
        }

        // Lavanda (21) - LEITO 01 a LEITO 20
        for ($i = 1; $i <= 20; $i++) {
            $leitos[] = [
                'cod' => 299 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 21,
            ];
        }

        // NA para vários setores (320-339)
        $setoresNA = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22];
        $codNA = 320;
        foreach ($setoresNA as $setor) {
            $leitos[] = [
                'cod' => $codNA,
                'descricao' => 'NA',
                'cod_setor' => $setor,
            ];
            $codNA++;
        }

        // Ortopedia (23) - LEITO 01 a LEITO 17
        for ($i = 1; $i <= 17; $i++) {
            $leitos[] = [
                'cod' => 339 + $i,
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 23,
            ];
        }

        // NA para Ortopedia (357)
        $leitos[] = [
            'cod' => 357,
            'descricao' => 'NA',
            'cod_setor' => 23,
        ];

        // Lírios (3) - Continuação LEITO 17 a LEITO 22
        for ($i = 17; $i <= 22; $i++) {
            $leitos[] = [
                'cod' => 357 + ($i - 16),
                'descricao' => 'LEITO ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'cod_setor' => 3,
            ];
        }

        foreach ($leitos as $leito) {
            DB::table('leito')->updateOrInsert(
                ['cod' => $leito['cod']],
                [
                    'descricao' => $leito['descricao'],
                    'cod_setor' => $leito['cod_setor'],
                ]
            );
        }

        $this->command->info('Leitos criados/atualizados: ' . count($leitos) . ' registros');
    }
}


