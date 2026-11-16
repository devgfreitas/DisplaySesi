<?php
use App\Services\RegrasTv;
use PHPUnit\Framework\TestCase;

/* Demonstra o bloqueio de conteúdo privado no modo público. */
class RegrasTvTest extends TestCase
{
    private function base(array $o = []): array
    {
        return array_merge(['tipo' => 'aviso', 'visibilidade' => 'publico', 'publico_alvo' => 'geral', 'texto' => 'Reunião de pais na sexta-feira'], $o);
    }

    public function testAvisoPublicoPodeIrParaATv(): void
    {
        $this->assertNull(RegrasTv::validar($this->base()));
    }

    public function testRecadoIndividualNuncaVaiParaATv(): void
    {
        $this->assertNotNull(RegrasTv::validar($this->base(['tipo' => 'recado_individual'])));
    }

    public function testProvaETarefaNaoVaoParaATv(): void
    {
        $this->assertNotNull(RegrasTv::validar($this->base(['tipo' => 'prova'])));
        $this->assertNotNull(RegrasTv::validar($this->base(['tipo' => 'tarefa'])));
    }

    public function testConteudoInternoOuPrivadoEhBloqueado(): void
    {
        $this->assertNotNull(RegrasTv::validar($this->base(['visibilidade' => 'interno'])));
        $this->assertNotNull(RegrasTv::validar($this->base(['visibilidade' => 'privado'])));
    }

    public function testConteudoDeTurmaEhBloqueado(): void
    {
        $this->assertNotNull(RegrasTv::validar($this->base(['publico_alvo' => 'turmas'])));
    }

    /** @dataProvider textosSensiveis */
    public function testTextoComDadosSensiveisEhBloqueado(string $texto): void
    {
        $this->assertNotNull(RegrasTv::validar($this->base(['texto' => $texto])), $texto);
    }

    public function textosSensiveis(): array
    {
        return [
            'telefone' => ['Ligue para (18) 99999-0000'],
            'cpf'      => ['CPF 123.456.789-09'],
            'email'    => ['Escreva para pai@exemplo.com'],
            'faltas'   => ['O aluno tem 5 faltas'],
            'notas'    => ['Divulgação das notas do bimestre'],
        ];
    }

    public function testTextoNormalNaoEhBloqueado(): void
    {
        $this->assertNull(RegrasTv::conteudoSensivel('Feira de Ciências no ginásio, sábado às 9h'));
    }
}
