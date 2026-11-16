<?php
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testConteudoAprovadoDentroDaValidadeEstaNoAr(): void
    {
        $p = ['status' => 'aprovado', 'inicio_exibicao' => date('Y-m-d H:i:s', strtotime('-1 day')), 'fim_exibicao' => date('Y-m-d H:i:s', strtotime('+1 day'))];
        $this->assertSame('no_ar', estado($p)[0]);
    }

    public function testConteudoExpiradoSaiDaExibicao(): void
    {
        $p = ['status' => 'aprovado', 'inicio_exibicao' => null, 'fim_exibicao' => date('Y-m-d H:i:s', strtotime('-1 minute'))];
        $this->assertSame('expirado', estado($p)[0]);
    }

    public function testConteudoFuturoFicaAgendado(): void
    {
        $p = ['status' => 'aprovado', 'inicio_exibicao' => date('Y-m-d H:i:s', strtotime('+2 days')), 'fim_exibicao' => null];
        $this->assertSame('agendado', estado($p)[0]);
    }

    public function testConteudoNaoAprovadoNuncaEstaNoAr(): void
    {
        $p = ['status' => 'em_revisao', 'inicio_exibicao' => null, 'fim_exibicao' => null];
        $this->assertSame('em_revisao', estado($p)[0]);
    }

    public function testLinhasDoCardapio(): void
    {
        $this->assertSame([['Seg', 'arroz, feijão']], linhasCardapio('Seg - arroz, feijão'));
    }

    public function testDatasEmPortugues(): void
    {
        $this->assertSame('15/11/2026', d_br('2026-11-15'));
        $this->assertSame('2026-11-15T08:00', dt_local('2026-11-15 08:00:00'));
        $this->assertSame('2026-11-15 08:00:00', de_local('2026-11-15T08:00'));
    }
}
