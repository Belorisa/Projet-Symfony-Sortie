<?php

namespace App\Command;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-sortie',description: 'Mise a jour des sorties' )]
class UpdateSortie extends Command
{

    public function __construct(
        private SortieRepository $sortieRepository,
        private EntityManagerInterface $entityManager
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime();

        $sorties = $this->sortieRepository->findAll();

        foreach ($sorties as $sortie) {
            if($sortie->getEtat()!='ANNULER')
            {
                $output->writeln("Les sortie détecté");
                if($sortie->getDateHeureDebut() <= $now && $sortie->getDateHeureFin() >= $now){
                    $sortie->setEtat('EN COURS');
                }
                elseif ($sortie->getDateHeureFin() < $now)
                {
                    $sortie->setEtat('PASSE');
                }

            }

        }

        $this->entityManager->flush();
        $output->writeln("Les sortie on été mise a jour");


        return Command::SUCCESS;
    }


}