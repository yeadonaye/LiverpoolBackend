<?php
class Participer{
    private int $idParticipation;
    private string $poste;
    private int $note;
    private bool $titulaireOuPas;

    public function __construct(int $idParticipation, string $poste, int $note, bool $titulaireOuPas){
        $this->idParticipation = $idParticipation;
        $this->poste = $poste;
        $this->note = $note;
        $this->titulaireOuPas = $titulaireOuPas;
    }

    //getters

    public function getIdParticipation(): int{
        return $this->idParticipation;
    }

    public function getPoste(): string{
        return $this->poste;
    }

    public function getNote(): int{
        return $this->note;
    }

    public function getTitulaireOuPas(): bool{
        return $this->titulaireOuPas;
    }

    //setters

    public function setPoste(string $poste): void{
        $this->poste = $poste;
    }

    public function setNote(int $note): void{
        $this->note = $note;
    }

    public function setTitulaireOuPas(bool $titulaireOuPas): void{
        $this->titulaireOuPas = $titulaireOuPas;
    }

}
?>