<?php

if ($_GET['pilote'] || $_GET['speciale']){

    $piloteId = $_GET['pilote'];
    $specialeId = $_GET['speciale'];

    function timer ($piloteId, $specialeId) {

        if ($specialeId > 2 || $specialeId < 1){
            return false;
        }

        /*
         * Connexion à la base de données base1
         */
        $db1 = new PDO('mysql:host=localhost;dbname=base1', 'kimt', '1992');
        $db1->exec('SET NAMES UTF8');
        $db1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /*
            * Selection le temps en fonction des parametres
            */
        $requete = $db1->prepare('SELECT *  FROM temps WHERE id_pilote = :id_pilote AND id_speciale = :id_speciale AND depart IS NOT NULL AND arrivee IS NOT NULL');

        $requete->bindParam('id_pilote', $piloteId, PDO::PARAM_INT);
        $requete->bindParam('id_speciale', $specialeId, PDO::PARAM_INT);

        $requete->execute();
        $time =$requete->fetch();

        /*
         * Remplace les caractères allant de 0 jsuqu'a 11 et les met au format date
         */
        $td = date('H:i:s', strtotime(( substr_replace( $time->depart, '', 0, 11))));
        $ta = date('H:i:s', strtotime(( substr_replace( $time->arrivee, '', 0, 11))));



        /*
         * Met en milliseconde
         */
        $ams = $time->ams / 1000;

        /*
         * Met au format datetime le temps de départ et le temps d'arrivée
         */
        $ttd = new DateTime($td);
        $tta = new DateTime($ta);

        /*
         * Fait la différence entre le temps de départ et le temps d'arrivée
         */
        $diff = $ttd->diff($tta);

        /*
         *  Met en milliseconde les minutes et les secondes de la différence
         */
        $i = $diff->i * 60000;
        $s = $diff->s * 1000;

        /*
         * Additionne en milliseconde les minutes, les secondes et les millisecondes
         */
        $temps =$i + $s + $ams;

        if ($temps) {

            $tempsA = $ta.':'.$ams;
            $date = substr_replace( $time->depart, '', 10, 9);
            $a = substr_replace($date, '', 4, 6);
            $m = substr_replace($date, '', 0, 5);
            $j = substr_replace($m, '', 0, 3);
            $m = substr_replace($m, '', 2, 3);
            $dates = $j.'/'.$m.'/'.$a;

            $db2 = new PDO('mysql:host=localhost;dbname=base2', 'kimt', '1992');
            $db2->exec('SET NAMES UTF8');
            $db2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


            $requete2 = $db2->prepare('SELECT *  FROM temps WHERE id_pilote = :id_pilote AND id_speciale = :id_speciale AND dates = :dates');

            $requete2->bindParam('id_pilote', $piloteId, PDO::PARAM_INT);
            $requete2->bindParam('id_speciale', $specialeId, PDO::PARAM_INT);
            $requete2->bindParam('dates', $dates);

            $requete2->execute();
            $temsPilote =$requete2->fetch();

            if ($temsPilote) {
                return false;
            } else {
                /*
            * Insertion des données dans la base de données base2
            */
                $requete3 = $db2->prepare('INSERT INTO temps(id_pilote, id_speciale, dates, temps, depart, arrivee) VALUES(:id_pilote, :id_speciale, :dates, :temps, :depart, :arrivee)');

                $requete3->bindParam('id_pilote', $piloteId, PDO::PARAM_INT);
                $requete3->bindParam('id_speciale', $specialeId, PDO::PARAM_INT);
                $requete3->bindParam('dates', $dates);
                $requete3->bindParam('temps', $temps);
                $requete3->bindParam('depart', $td);
                $requete3->bindParam('arrivee', $tempsA);

                $requete3->execute();
            };
        };

    };

    timer($piloteId, $specialeId);

} elseif (empty($_GET)) {

    function timerAll () {

        /*
         * Connexion à la base de données base1
         */
        $db1 = new PDO('mysql:host=localhost;dbname=base1', 'kimt', '1992');
        $db1->exec('SET NAMES UTF8');
        $db1->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $requete = $db1->query('SELECT *  FROM temps WHERE depart IS NOT NULL AND arrivee IS NOT NULL');

        $timeAll = $requete->fetchAll();

        foreach ($timeAll as $time) {

            /*
             * Remplace les caractères allant de 0 jsuqu'a 11 et les met au format date
             */
            $td = date('H:i:s', strtotime(( substr_replace( $time->depart, '', 0, 11))));
            $ta = date('H:i:s', strtotime(( substr_replace( $time->arrivee, '', 0, 11))));

            /*
             * Met en milliseconde
             */
            $ams = $time->ams / 1000;

            /*
             * Met au format datetime le temps de départ et le temps d'arrivée
             */
            $ttd = new DateTime($td);
            $tta = new DateTime($ta);

            /*
             * Fait la différence entre le temps de départ et le temps d'arrivée
             */
            $diff = $ttd->diff($tta);

            /*
             *  Met en milliseconde les minutes et les secondes de la différence
             */
            $i = $diff->i * 60000;
            $s = $diff->s * 1000;

            /*
            * Additionne en milliseconde les minutes, les secondes et les millisecondes
            */
            $temps =$i + $s + $ams;

            /*
             * Met à jour le temps des pilotes en milliseconde
             */
            $requete2 = $db1->prepare('UPDATE temps SET temps = :temps WHERE id = :id');

            $requete2->bindParam('temps', $temps);
            $requete2->bindParam('id', $time->id);

            $requete2->execute();
        }

    }

    timerAll();
} else {
    return false;
}


