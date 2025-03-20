<?php

namespace App\Controller;

use App\Form\DatabaseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdminSaveDBController extends AbstractController
{
    #[Route('/admin/database', name: 'admin_database')]
    public function index(Request $request): Response
    {
        $host = '127.0.0.1';
        $username = 'root';
        $password = 'Geor-2023';
        $dbname = 'sgpc';
        $db = new \mysqli($host, $username, $password, $dbname);
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }
        mysqli_set_charset($db, "utf8");

        $form = $this->createForm(DatabaseType::class, null, [
            'method' => 'POST',]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $restoreFile = $form->get('uploadedDatabaseFile')->getData();

            $file = fopen($restoreFile, "r");
            $line = fgets($file);
            if (!stristr($line, '#**************** BASE DE DONNEES')) {
                fclose($file);
                $this->addFlash('error',
                    'Il ne s\'agit pas d\'un fichier de sauvegarde Khemeia.');
                return $this->redirectToRoute('admin_database');
            } else {
                fclose($file);
                $file = fopen($restoreFile, "r");
                $ok = "";
                $error = "";
                while (!feof($file)) {
                    $line = fgets($file);
                    while ($line[0] != '#' && !stristr($line, ';') && !feof($file))
                        $line .= fgets($file);
                    if (mysqli_query($db, $line))
                        $ok .= "1";
                    else {
                        $ok .= "0";
                        $error .= " -- " . htmlspecialchars($line);
                    }
                }
                fclose($file);


                if (strrpos($ok, '0')) {
                    $this->addFlash('success',
                        'La restauration est terminée ! ' .
                        strlen($ok) . " requêtes ont été exécutées avec "
                        . substr_count($ok, '0')
                        . " erreur(s). Les requêtes suivantes n'ont pas été éxecutées" . $error);
                } else
                    $this->addFlash('success',
                        'La restauration est terminée ! ' .
                        strlen($ok) . " requêtes ont été exécutées sans erreurs.");
            }
            return $this->redirectToRoute('admin_database');

        }
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('admin/save.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/save', name: 'admin_save')]
    public function save(): Response
    {
        // Connexion à la base de données
        $host = '127.0.0.1';
        $username = 'root';
        $password = 'Geor-2023';
        $dbname = 'sgpc';
        $db = new \mysqli($host, $username, $password, $dbname);
        if ($db->connect_error) {
            throw $this->createNotFoundException("Connexion à la base de données échouée: " . $db->connect_error);
        }
        mysqli_set_charset($db, "utf8");

        // Liste des tables à sauvegarder
        $liste_tables_a_verifier = array(
            'cautionaryAdvice',
            'dangerNote',
            'dangerSymbol',
            'analysisfile',
            'securityfile',
            'privilege',
            'property',
            'site',
            'status',
            'supplier',
            'type',
            'ACL',
            'chimicalProduct',
            'stock',
            'cupboard',
            'productByCautionaryAdvice',
            'productByDangerNote',
            'productByDangerSymbol',
            'productByType',
            'shelvingUnit',
            'storageCard',
            'user',
            'movedhistory',
            'tracability',
            'controlbytype'
        );

        // Vérifier quelles tables existent réellement
        $liste_tables = array();
        foreach ($liste_tables_a_verifier as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $result = mysqli_query($db, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $liste_tables[] = $table;
            }
        }

        // Si aucune table n'existe, afficher un message d'erreur
        if (empty($liste_tables)) {
            throw $this->createNotFoundException("Aucune des tables spécifiées n'existe dans la base de données '$dbname'");
        }

        $nomsql = $dbname . "_le_" . date("d_m_Y_\a_H\hi") . ".sql";
        $chemin = $this->getParameter('save_db');
        $path = $chemin . $nomsql;

        // Génération du contenu du fichier SQL
        $fd = "#**************** BASE DE DONNEES " . $dbname . " ****************" . "\n" . date("\#\ \L\\e\ \:\ d\ m\ Y\ \a\ H\h\ i") . "\n";

        if (isset($_SERVER['SERVER_NAME'])) {
            $fd .= "# Serveur : " . $_SERVER['SERVER_NAME'] . "\n";
        }

        $fd .= "# Version PHP : " . $this->php_version() . "\n";
        $fd .= "# Version mySQL : " . $this->mysql_version($db) . "\n";
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $fd .= "# IP Client : " . $_SERVER['REMOTE_ADDR'] . "\n";
        }
        $fd .= "# Fichier SQL compatible PHPMyadmin\n#\n";
        $fd .= "# ******* debut du fichier ********\n";
        $fd .= "#\n# Nettoyage de la base\n#\n";

        // Ajout des instructions DROP TABLE
        $arrayReversed = array_reverse($liste_tables);
        foreach ($arrayReversed as $table) {
            $fd .= "DROP TABLE IF EXISTS `$table`;\n";
        }

        $fd .= "#\n# Construction de la base \n#\n";

        // Génération du SQL pour chaque table
        foreach ($liste_tables as $table) {
            $fd .= "#\n# Structure de la table $table\n#\n";

            // Récupération de la structure de la table
            $query = "SHOW CREATE TABLE $table";
            $resCreate = mysqli_query($db, $query);
            if (!$resCreate) {
                $fd .= "# Erreur lors de la récupération de la structure de $table: " . mysqli_error($db) . "\n";
                continue;
            }

            $row = mysqli_fetch_array($resCreate);
            $schema = $row[1] . ";";
            $fd .= "$schema\n";

            $fd .= "#\n# Données de $table\n#\n";
            $query = "SELECT * FROM $table";
            $resData = mysqli_query($db, $query);

            if (!$resData) {
                $fd .= "# Problème avec les données de $table: " . mysqli_error($db) . "\n";
            } else {
                if (mysqli_num_rows($resData) > 0) {
                    $num_fields = mysqli_field_count($db);
                    $sInsert = "INSERT INTO $table values ";

                    while ($rowdata = mysqli_fetch_row($resData)) {
                        $lesDonnees = "";
                        for ($mp = 0; $mp < $num_fields; $mp++) {
                            $uneDonnee = $rowdata[$mp];
                            if ($uneDonnee === null) {
                                $lesDonnees .= "null";
                            } else {
                                $lesDonnees .= "'" . mysqli_real_escape_string($db, $rowdata[$mp]) . "'";
                            }

                            if ($mp < $num_fields - 1) {
                                $lesDonnees .= ", ";
                            }
                        }
                        $lesDonnees = "$sInsert($lesDonnees);\n";
                        $fd .= "$lesDonnees";
                    }
                }
            }
        }

        $fd .= "#********* fin du fichier ***********";

        // Écriture dans le fichier
        file_put_contents($path, $fd);

        // Création de la réponse avec les en-têtes appropriés
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $nomsql
        );

        return $response;
    }

    private function php_version(): string
    {
        preg_match('`([0-9]{1,2}).([0-9]{1,2})`', phpversion(), $match);
        if (isset($match) && !empty($match[1])) {
            if (!isset($match[2]))
                $match[2] = 0;
        }
        if (!isset($match[3]))
            $match[3] = 0;
        return $match[1] . "." . $match[2] . "." . $match[3];
    }

    private function mysql_version($db): string
    {
        $result = mysqli_query($db, 'SELECT VERSION() AS version');
        if ($result != FALSE && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $match = explode('.', $row['version']);
        } else {
            $result = mysqli_query($db, 'SHOW VARIABLES LIKE \'version\'');
            if ($result != FALSE && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_row($result);
                $match = explode('.', $row[1]);
            }
        }
        if (!isset($match) || !isset($match[0]))
            $match[0] = 3;
        if (!isset($match[1]))
            $match[1] = 21;
        if (!isset($match[2]))
            $match[2] = 0;
        return $match[0] . "." . $match[1] . "." . $match[2];
    }
}
