<?php
// src/Service/CasWrapper.php

namespace App\Service;

class CasWrapper
{
    public static function initCAS($serveur, $port, $uri, $startSession)
    {
        try {
            // Désactiver la sortie de phpCAS
            ob_start();

            // Vérifier si les constantes CAS sont définies
            if (!defined('CAS_VERSION_2_0')) {
                define('CAS_VERSION_2_0', '2.0');
            }

            // Initialiser phpCAS avec des valeurs sécurisées
            if (class_exists('phpCAS')) {
                \phpCAS::client(CAS_VERSION_2_0, $serveur, intval($port), $uri, (bool)$startSession);
                \phpCAS::setNoCasServerValidation();
            } else {
                throw new \Exception("La classe phpCAS n'existe pas");
            }

            // Récupérer et ignorer toute sortie
            ob_end_clean();
            return true;
        } catch (\Exception $e) {
            // Récupérer et ignorer toute sortie
            ob_end_clean();

            // Log l'erreur ou la retourner
            error_log("Erreur CAS: " . $e->getMessage());
            return false;
        }
    }
}