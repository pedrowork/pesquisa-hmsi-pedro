<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerguntasDescricaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perguntas = [
            [
                'cod' => 1,
                'descricao' => 'Como classificaria o serviço da equipe de enfermagem?',
                'cod_setor_pesquis' => 2,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 2,
                'descricao' => 'Como classificaria o atendimento e serviço prestado pelos médicos?',
                'cod_setor_pesquis' => 3,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 3,
                'descricao' => 'Como classificaria o atendimento e clareza das informações referente a recepção por onde entrou?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 4,
                'descricao' => 'Se teve atendimento, como classificaria o serviço da equipe de fisioterapia?',
                'cod_setor_pesquis' => 2,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 5,
                'descricao' => 'Como avalia a qualidade das refeições oferecidas no hospital?',
                'cod_setor_pesquis' => 5,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 6,
                'descricao' => 'Se obteve atendimento, como você avalia a eficiência da equipe de serviço social em compreender e atender às suas necessidades durante sua estadia no hospital?',
                'cod_setor_pesquis' => 4,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 7,
                'descricao' => 'Como avalia o ambiente físico do quarto e as condições de conforto durante sua estadia?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 8,
                'descricao' => 'Como classificaria a higienização do setor?',
                'cod_setor_pesquis' => 1,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 9,
                'descricao' => 'Como você avalia a qualidade do atendimento de forma geral?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 10,
                'descricao' => 'Em uma escala de 0 a 10, quanto você recomendaria o Hospital e Maternidade Santa Isabel para um familiar ou amigo?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 3,
            ],
            [
                'cod' => 11,
                'descricao' => 'Voçê se sentiu cuidado de forma segura e especial?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 2,
            ],
            [
                'cod' => 12,
                'descricao' => 'Teria alguma observação a fazer?',
                'cod_setor_pesquis' => 7,
                'cod_tipo_pergunta' => 4,
            ],
        ];

        foreach ($perguntas as $pergunta) {
            DB::table('perguntas_descricao')->updateOrInsert(
                ['cod' => $pergunta['cod']],
                [
                    'descricao' => $pergunta['descricao'],
                    'cod_setor_pesquis' => $pergunta['cod_setor_pesquis'],
                    'cod_tipo_pergunta' => $pergunta['cod_tipo_pergunta'],
                ]
            );
        }

        $this->command->info('Perguntas criadas/atualizadas: ' . count($perguntas) . ' registros');
    }
}


