<?php

namespace App\Message;

class ReminderMessage
{
    private int $idSortie;
    private int $idUser;

    public function __construct(int $idSortie,int $idUser)
    {
        $this->idSortie = $idSortie;
        $this->idUser = $idUser;

    }

    public function getIdSortie(): int
    {
        return $this->idSortie;
    }

    public function getIdUser(): int
    {
        return $this->idUser;
    }
}