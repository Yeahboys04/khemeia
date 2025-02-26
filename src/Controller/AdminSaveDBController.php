<?php

namespace App\Controller;

use App\Form\DatabaseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            if (!stristr($line,'#**************** BASE DE DONNEES')){
                fclose($file);
                $this->addFlash('error',
                    'Il ne s\'agit pas d\'un fichier de sauvegarde Khemeia.');
                return $this->redirectToRoute('admin_database');
            }
            else {
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
        return $this->render('admin/save.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/save', name: 'admin_save')]
    public function save(): BinaryFileResponse
    {
        //ini_set('memory_limit', '1024M');
        $host = '127.0.0.1';
        $username = 'root';
        $password = 'root';
        $dbname = 'sgpc';
        $GLOBALS['db'] = new \mysqli($host, $username, $password, $dbname);
        if ($GLOBALS['db']->connect_error) {
            die("Connection failed: " . $GLOBALS['db']->connect_error);
        }
        mysqli_set_charset($GLOBALS['db'], "utf8");
        $liste_tables = array(
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


        $nomsql = $dbname."_le_".date("d_m_Y_\a_H\hi").".sql";
        $now = date('D, d M Y H:i:s') . ' GMT';

        header('Content-Type: text/x-csv');
        header('Expires: ' . $now);
        if (isset($_SERVER['HTTP_USER_AGENT'])){
            if (preg_match('`MSIE`', $_SERVER['HTTP_USER_AGENT'])){
                header('Content-Disposition: inline; filename="' . $nomsql . '"');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            }else{
                header('Content-Disposition: attachment; filename="' . $nomsql . '"');
                header('Pragma: no-cache');
            }
        }
        $fd = "#**************** BASE DE DONNEES ".$dbname." ****************"."\n".date("\#\ \L\\e\ \:\ d\ m\ Y\ \a\ H\h\ i")."\n";
        if (isset($_SERVER['SERVER_NAME'])){
            $fd .= "# Serveur : ".$_SERVER['SERVER_NAME']."\n";
        }

        $fd .= "# Version PHP : " . $this->php_version()."\n";
        $fd .= "# Version mySQL : " . $this->mysql_version()."\n";
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $fd .= "# IP Client : " . $_SERVER['REMOTE_ADDR'] . "\n";
        }
        $fd .= "# Fichier SQL compatible PHPMyadmin\n#\n";
        $fd .= "# ******* debut du fichier ********\n";
        $fd .= "#\n# Nettoyage de la base\n#\n";
        $i = '0';
        $arrayReversed = array_reverse($liste_tables);
        while ($i < count($arrayReversed)){
            //$fd .= "#\n# Nettoyage de la base\n#\n";
            $temp = $arrayReversed[$i];
            $fd .= "DROP TABLE IF EXISTS `$temp`;\n";
            $i++;
        }
        $fd .= "#\n# Construction de la base \n#\n";
        $j = '0';
        while ($j < count($liste_tables)){
            $temp = $liste_tables[$j];
            $fd .= "#\n# Structure de la table $temp\n#\n";
            //$fd .= "DROP TABLE IF EXISTS `$temp`;\n";
            // requete de creation de la table
            $query = "SHOW CREATE TABLE $temp";
            $resCreate = mysqli_query($GLOBALS['db'], $query);
            $row = mysqli_fetch_array($resCreate);
            $schema = $row[1].";";
            $fd.="$schema\n";
            $fd.="#\n# Données de $temp\n#\n";
            $query = "SELECT * FROM $temp";
            $resData = mysqli_query($GLOBALS['db'], $query);
            //peut survenir avec la corruption d'une table, on prévient
            if (!$resData) {
                $fd .= "Problème avec les données de $temp, corruption possible !\n";
            }
            else{
                if (mysqli_num_rows($resData) > 0){
                    $sFieldnames = "";
                    $num_fields = mysqli_field_count($GLOBALS['db']);
                    $sInsert = "INSERT INTO $temp $sFieldnames values ";
                    while ($rowdata = mysqli_fetch_row($resData)){
                        $lesDonnees = "";
                        for ($mp = 0; $mp < $num_fields; $mp++){
                            //on vérifie si la valeur null est rentrée dans la base
                            $uneDonnee = mysqli_real_escape_string($GLOBALS['db'], $rowdata[$mp]);
                            if ($uneDonnee == null) {
                                $lesDonnees .= "null";
                            }
                            else {
                                $lesDonnees .= "'" . mysqli_real_escape_string($GLOBALS['db'], $rowdata[$mp]) . "'";
                            }
                            //on ajoute à la fin une virgule si nécessaire
                            if ($mp<$num_fields-1)
                                $lesDonnees .= ", ";
                        }
                        $lesDonnees = "$sInsert($lesDonnees);\n";
                        $fd.="$lesDonnees";
                    }
                }
            }
            $j++;
        }
        $fd.="#********* fin du fichier ***********";
        $chemin = $this->getParameter('save_db');
        $path = $chemin.$nomsql;
        file_put_contents($path, $fd);

        //Quoi qu'il arrive on rend la page initiale
        return new BinaryFileResponse($path);
    }

    private function php_version(): string
    {
        preg_match('`([0-9]{1,2}).([0-9]{1,2})`', phpversion(), $match);
        if (isset($match) && !empty($match[1]))
        {
            if (!isset($match[2]))
                $match[2] = 0;
        }
        if (!isset($match[3]))
            $match[3] = 0;
        return $match[1] . "." . $match[2] . "." . $match[3];
    }

    private function mysql_version(): string
    {
        $result = mysqli_query($GLOBALS['db'], 'SELECT VERSION() AS version');
        if ($result != FALSE && mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_array($result);
            $match = explode('.', $row['version']);
        }else{
            $result = mysqli_query($GLOBALS['db'], 'SHOW VARIABLES LIKE \'version\'');
            if ($result != FALSE && mysqli_num_rows($result) > 0){
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