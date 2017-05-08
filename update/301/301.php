<?php
    require_once("../../includes/config.JTL-Shop.ini.php");
    require_once(PFAD_ROOT . "includes/" . "defines.php");

    //existiert Konfiguration?
    if (!defined('DB_HOST')) {
        die("Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!");
    }
    if (!defined('DB_NAME')) {
        die("Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!");
    }
    if (!defined('DB_USER')) {
        die("Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!");
    }
    if (!defined('DB_PASS')) {
        die("Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!");
    }

    require_once(PFAD_ROOT . PFAD_CLASSES_CORE."class.core.NiceDB.php");
    require_once(PFAD_ROOT . PFAD_INCLUDES."tools.Global.php");
    require_once(PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . "dbupdater_inc.php");
    
    //datenbankverbindung aufbauen
    $DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    session_name("eSIdAdm");
    session_start();
    if (!isset($_SESSION['AdminAccount'])) {
        header('Location: ' . URL_SHOP . "/" . PFAD_ADMIN . "index.php");
        exit;
    }
    
    // ##### Anfang Script
    
    
    // Vorbereitung
    $nStartStamp = time();
    if (intval(ini_get('max_execution_time')) < 320) {
        @ini_set('max_execution_time', 320);
    }
    $nMaxLaufzeit = intval(ini_get('max_execution_time')) / 2;  // Maximale Laufzeit die das Script laufen darf
    //$nMaxLaufzeit = 2;
    $nEndeStamp = $nStartStamp + $nMaxLaufzeit;
    $cSQLDatei = "update1.sql";
    
    // ### Main Script
    if (intval($_GET['nFirstStart']) == 1) {
        resetteUpdateDB();                  // Fügt Spalten hinzu die vielleicht noch nicht vorhanden sind und setzt alle wichtigen Spalten auf 0
        updateZeilenBis($cSQLDatei);     // Läuft die Datei durch und zählt die Reihen. Danach wird die Anzahl in der DB hinterlegt.
    }
    
    $oVersion = $GLOBALS['DB']->executeQuery("SELECT * FROM tversion", 1);
    
    if (!file_exists($cSQLDatei)) {
        header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=1");
        exit();
    }

    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 1", 4);
    $nRow = 1;
    
    switch ($oVersion->nTyp) {
        case 1:    // SQL
            $oAnzahlBoxen = $GLOBALS['DB']->executeQuery("SELECT count(*) as nAnzahl
															FROM tboxen", 1);
        
            $file_handle = @fopen($cSQLDatei, "r");
            if ($oVersion->nZeileVon <= $oVersion->nZeileBis) {
                while ($cData = fgets($file_handle)) {
                    if (time() < $nEndeStamp) {
                        if ($nRow > $oVersion->nZeileBis) {//updateFertig(302); // Fertig
                            naechsterUpdateStep(2, $oAnzahlBoxen->nAnzahl);
                        }
                        
                        if ($nRow >= $oVersion->nZeileVon) {
                            // Wurde bei einem SQL 3x ein Fehler ausgegeben?
                            if (intval($oVersion->nFehler) >= 3) {
                                @fclose($file_handle);
                                header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=999");
                                exit();
                            }
                                                     
                            // SQL ausführen
                            $GLOBALS['DB']->executeQuery($cData, 4);
                    
                            $nErrno = $GLOBALS['DB']->DB()->errno;
                            
                            if (!$nErrno || $nErrno == 1062 || $nErrno == 1060 || $nErrno == 1267) {
                                writeLog("update.log", $nRow . ": " . $cData . " erfolgreich ausgeführt. MySQL Errno: " . $nErrno . " - " . str_replace("'", "", $GLOBALS['DB']->DB()->error), LOGLEVEL_DEBUG);
                                $nRow++;
                                $GLOBALS['DB']->executeQuery("UPDATE tversion SET nZeileVon = " . $nRow . ", nFehler=0, cFehlerSQL=''", 4);
                                
                                if ($nRow > $oVersion->nZeileBis) {
                                    @fclose($file_handle);
                                    naechsterUpdateStep(2, $oAnzahlBoxen->nAnzahl);
                                }
                            } else {
                                if (strpos(strtolower($cData), "alter table")) {// Alter Table darf nicht nochmal ausgeführt werden
                                    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nFehler=3, cFehlerSQL='Zeile " . $nRow . ": " . str_replace("'", "", $GLOBALS['DB']->DB()->error) . "'", 4);
                                } else {
                                    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nFehler=nFehler+1, cFehlerSQL='Zeile " . $nRow . ": " . str_replace("'", "", $GLOBALS['DB']->DB()->error) . "'", 4);
                                }
                                
                                writeLog("update.log", "Fehler in Zeile " . $nRow . ": " . str_replace("'", "", $GLOBALS['DB']->DB()->error), LOGLEVEL_DEBUG);
                                @fclose($file_handle);
                                $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 0", 4);
                                header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=1");
                                exit();
                            }
                        } else {
                            $nRow++;
                        }
                    } else {
                        break;
                    }
                }
                
                if ($nRow == $oVersion->nZeileBis) {// Fertig!
                    //updateFertig(302); // Fertig
                    naechsterUpdateStep(2, $oAnzahlBoxen->nAnzahl);
                }
            } else {// Fertig!
                //updateFertig(302); // Fertig
                naechsterUpdateStep(2, $oAnzahlBoxen->nAnzahl);
            }
            break;
        
        case 2:    // Boxenverwaltung
            if ($oVersion->nZeileVon <= $oVersion->nZeileBis) {
                unset($hRes);
                $hRes = $GLOBALS['DB']->executeQuery("SELECT *
														FROM tboxen
														ORDER BY kSeite ASC
														LIMIT " . ($oVersion->nZeileVon - 1) . ", 10000", 10);
                    
                if ($hRes->num_rows == 0) {
                    // Fertig

                    $GLOBALS['DB']->executeQuery("ALTER TABLE `tboxen` DROP `kSeite`, DROP `nSort`, DROP `bAktiv`", 3);
                    updateFertig(302); // Fertig
                }
            
                writeLog("update.log", "### Boxenverwaltung...", LOGLEVEL_DEBUG);
                writeLog("update.log", "Anzahl Boxen: " . $oVersion->nZeileBis, LOGLEVEL_DEBUG);
                
                while ($oBox = $hRes->fetch_object()) {
                    if (time() < $nEndeStamp) {
                        writeLog("update.log", "Box " . $oVersion->nZeileVon . " / " . $oVersion->nZeileBis . " kBox: " . $oBox->kBox, LOGLEVEL_DEBUG);

                        for ($i = 0; $i <= 31; $i++) {
                            $bAktiv = false;
                            if ($oBox->kSeite == $i || $oBox->kSeite == 0) {
                                $bAktiv = $oBox->bAktiv;
                            }
                            
                            $oBoxTmp = new stdClass();
                            $oBoxTmp->kBox        = $oBox->kBox;
                            $oBoxTmp->kSeite    = $i;
                            $oBoxTmp->nSort        = $oBox->nSort;
                            $oBoxTmp->bAktiv    = intval($bAktiv);
                            
                            $GLOBALS['DB']->insertRow("tboxensichtbar", $oBoxTmp);
                        }
                        
                        writeLog("update.log", "EffectedRows " . $nEffectedRows, LOGLEVEL_DEBUG);
                        $oVersion->nZeileVon++;
                        $GLOBALS['DB']->executeQuery("UPDATE tversion SET nZeileVon = " . $oVersion->nZeileVon . ", nFehler=0, cFehlerSQL=''", 4);
                    } else {
                        break;
                    }
                }
                
                if ($oVersion->nZeileVon > $oVersion->nZeileBis) {
                    $GLOBALS['DB']->executeQuery("ALTER TABLE `tboxen` DROP `kSeite`, DROP `nSort`, DROP `bAktiv`", 3);
                    updateFertig(302); // Fertig
                }
            } else {
                $GLOBALS['DB']->executeQuery("ALTER TABLE `tboxen` DROP `kSeite`, DROP `nSort`, DROP `bAktiv`", 3);
                updateFertig(302); // Fertig
            }
            break;
    }
    
    // Abschluss
    $GLOBALS['DB']->executeQuery("UPDATE tversion SET nInArbeit = 0", 4);
    @fclose($file_handle);
    header("Location: " . URL_SHOP . "/" . PFAD_ADMIN . "dbupdater.php?nErrorCode=-1");
    exit();
