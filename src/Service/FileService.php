<?php

namespace App\Service;

use App\Entity\Analysisfile;
use App\Entity\Securityfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * Traite le fichier de sécurité uploadé
     */
    public function processSecurityFile(UploadedFile $file): Securityfile
    {
        $filename = $this->fileUploader->upload(
            $file,
            $this->parameterBag->get('idSecurityFile_directory')
        );

        $securityFile = new Securityfile();
        $securityFile->setNameSecurityfile($filename);

        $this->entityManager->persist($securityFile);
        $this->entityManager->flush();

        return $securityFile;
    }

    /**
     * Traite le fichier d'analyse uploadé
     */
    public function processAnalysisFile(UploadedFile $file): Analysisfile
    {
        $filename = $this->fileUploader->upload(
            $file,
            $this->parameterBag->get('idAnalysisFile_directory')
        );

        $analysisFile = new Analysisfile();
        $analysisFile->setNameAnalysisfile($filename);

        $this->entityManager->persist($analysisFile);
        $this->entityManager->flush();

        return $analysisFile;
    }
}