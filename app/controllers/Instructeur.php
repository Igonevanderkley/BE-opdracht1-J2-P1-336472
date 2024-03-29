<?php

class Instructeur extends BaseController
{
    private $instructeurModel;

    public function __construct()
    {
        $this->instructeurModel = $this->model('InstructeurModel');
    }

    public function overzichtInstructeur()
    {
        $result = $this->instructeurModel->getInstructeurs();

        $allVehicles = "<a href='" . URLROOT . "/instructeur/alleVoertuigen'/>alle voertuigen</a>";

        $rows = "";
        foreach ($result as $instructeur) {

            $instructeurs = $this->instructeurModel->getInstructeurs();

            $aantalInstructeurs = sizeof($instructeurs);


            $date = date_create($instructeur->DatumInDienst);
            $formatted_date = date_format($date, 'd-m-Y');



            $rows .= "<tr>
                        <td>$instructeur->Voornaam</td>
                        <td>$instructeur->Tussenvoegsel</td>
                        <td>$instructeur->Achternaam</td>
                        <td>$instructeur->Mobiel</td>
                        <td>$formatted_date</td>            
                        <td>$instructeur->AantalSterren</td>  

                                  
                        <td>
                            <a href='" . URLROOT . "/instructeur/overzichtvoertuigen/$instructeur->Id'>
                                <i class='bi bi-car-front'></i>
                            </a>
                        </td>
                       

                      </tr>";
        }

        $data = [
            'title' => 'Instructeurs in dienst',
            'aantalInstructeurs' => $aantalInstructeurs,
            'rows' => $rows,
            'allVehicles' => $allVehicles
        ];

        $this->view('Instructeur/overzichtinstructeur', $data);
    }

    public function overzichtVoertuigen($instructeurId)
    {

        $instructeurInfo = $this->instructeurModel->getInstructeurById($instructeurId);

        $naam = $instructeurInfo->Voornaam . " " . $instructeurInfo->Tussenvoegsel . " " . $instructeurInfo->Achternaam;
        $datumInDienst = $instructeurInfo->DatumInDienst;
        $aantalSterren = $instructeurInfo->AantalSterren;

        $toevoegen = "<a href='" . URLROOT . "/instructeur/overzichtNietToegewezenVoertuigen/$instructeurId'>Toevoegen Voertuig</a>";

        $result = $this->instructeurModel->getToegewezenVoertuigen($instructeurId);


        $tableRows = "";
        if (empty($result)) {

            $tableRows = "<tr>
                            <td colspan='6'>
                                Er zijn op dit moment nog geen voertuigen toegewezen aan deze instructeur
                            </td>
                          </tr>";
        } else {

            foreach ($result as $voertuig) {


                $date_formatted = date_format(date_create($voertuig->Bouwjaar), 'd-m-Y');

                $tableRows .= "<tr>
                                    <td>$voertuig->Id</td>
                                    <td>$voertuig->TypeVoertuig</td>
                                    <td>$voertuig->Type</td>
                                    <td>$voertuig->Kenteken</td>
                                    <td>$date_formatted</td>
                                    <td>$voertuig->Brandstof</td>
                                    <td>$voertuig->RijbewijsCategorie</td> 
                                    <td>
                                    <a href='" . URLROOT . "/instructeur/updateVoertuig/$voertuig->Id/$instructeurId'>
                                    <img src = '/public/img/b_edit.png'>
                                    </a> 
                                    </td>   
                                    <td>
                                    <a href='" . URLROOT . "/instructeur/unassignInstructeur/$voertuig->Id/$instructeurId'>
                                    <img src = '/public/img/b_drop.png'>
                                    </a> 
                                    </td>    
                                    
                            </tr>";
            }
        }


        $data = [
            'title'     => 'Door instructeur gebruikte voertuigen',
            'tableRows' => $tableRows,
            'naam'      => $naam,
            'datumInDienst' => $datumInDienst,
            'aantalSterren' => $aantalSterren,
            'toevoegen' => $toevoegen,
            'deleteMessage' => isset($GLOBALS['deleted']) ? 'Het door u geselecteerde voertuig is verwijderd' : null,
        ];

        if (isset($GLOBALS['deleted'])) {
            header('Refresh:3; url=/Instructeur/overzichtVoertuigen/' . $instructeurId);
        }

        $this->view('Instructeur/overzichtVoertuigen', $data);
    }

    function updateVoertuig($Id, $instructeurId)
    {

        $voertuigInfo = $this->instructeurModel->getToegewezenVoertuig($Id, $instructeurId);

        $data = [
            'title' => 'Update Voertuig',
            'voertuigId' => $Id,
            'instructeurId' => $instructeurId,
            'voertuigInfo' => $voertuigInfo

        ];

        $this->view('Instructeur/updateVoertuig', $data);
    }
    function updateVoertuigSave($instructeurId, $voertuigId)
    {
        $toegewezen = $this->instructeurModel->getVoertuigInstructeur($voertuigId);

        $this->instructeurModel->updateVoertuig($voertuigId);

        if ($toegewezen) {
            $this->instructeurModel->updateInstructeur($voertuigId);
        } else {
            $this->instructeurModel->updateNietToegewezenInstructeur($voertuigId);
        }

        // header('Location: Instructeur/overzichtVoertuigen');


        $this->overzichtVoertuigen($instructeurId);
    }

    function overzichtNietToegewezenVoertuigen($instructeurId)
    {


        $nietToegewezenVoertuigen = $this->instructeurModel->getNietToegewezenVoertuigen();
        $instructeurInfo = $this->instructeurModel->getInstructeurById($instructeurId);
        // $voertuigId = $this->instructeurModel->getVoertuigId();

        $naam = $instructeurInfo->Voornaam . " " . $instructeurInfo->Tussenvoegsel . " " . $instructeurInfo->Achternaam;
        $datumInDienst = $instructeurInfo->DatumInDienst;
        $aantalSterren = $instructeurInfo->AantalSterren;

        $tableRows = "";
        if (empty($nietToegewezenVoertuigen)) {

            $tableRows = "<tr>
                            <td colspan='6'>
                                Er zijn geen voertuigen beschikbaar op dit moment
                    
                            </td>
                          </tr>";
        } else {

            foreach ($nietToegewezenVoertuigen as $voertuig) {


                $date_formatted = date_format(date_create($voertuig->Bouwjaar), 'd-m-Y');

                $tableRows .= "<tr>
                                  
                                    <td>$voertuig->TypeVoertuig</td>
                                    <td>$voertuig->Type</td>
                                    <td>$voertuig->Kenteken</td>
                                    <td>$date_formatted</td>
                                    <td>$voertuig->Brandstof</td>
                                    <td>$voertuig->RijbewijsCategorie</td>
                                    <td>
                                     <a href='" . URLROOT . "/instructeur/updateNietToegewezenVoertuig/$instructeurId/$voertuig->Id'>
                                    <img src = '/public/img/b_edit.png'>
                                    </a> 
                                    </td>

                                    <td>
                                    <a href='" . URLROOT . "/instructeur/deleteVoertuig/$instructeurId/$voertuig->Id'>
                                   <img src = '/public/img/b_drop.png'>
                                   </a> 
                                   </td>
                                    
                            </tr>";
            }
        }


        $data = [
            'title' => 'Alle beschikbare voertuigen',
            'nietToegewezenVoertuigen' => $nietToegewezenVoertuigen,
            'tableRows' => $tableRows,
            'naam'      => $naam,
            'datumInDienst' => $datumInDienst,
            'aantalSterren' => $aantalSterren,
            'deleteMessage' => isset($GLOBALS['deleted']) ? 'Het door u geselecteerde voertuig is verwijderd' : null,
        ];

        if (isset($GLOBALS['deleted'])) {
            header('Refresh:3; url=/Instructeur/overzichtNietToegewezenVoertuigen/' . $instructeurId);
        }

        $this->view('Instructeur/overzichtNietToegewezenVoertuig', $data);
    }


    function updateNietToegewezenVoertuig($instructeurId, $voertuigId)
    {
        $voertuigInfo = $this->instructeurModel->getNietToegewezenVoertuig($voertuigId);

        $data = [
            'title' => 'Update Voertuig',
            'voertuigId' => $voertuigId,
            'instructeurId' => $instructeurId,
            'voertuigInfo' => $voertuigInfo

        ];

        $this->view('Instructeur/UpdateVoertuig', $data);
    }

    function unassignInstructeur($voertuigId, $instructeurId)
    {
        $this->instructeurModel->unassignInstructeur($voertuigId, $instructeurId);

        $GLOBALS['deleted'] = true;

        $this->overzichtVoertuigen($instructeurId);
    }

    function deleteVoertuig($instructeurId, $voertuigId)
    {
        $this->instructeurModel->deleteVoertuig($voertuigId);

        $GLOBALS['deleted'] = true;

        $this->overzichtNietToegewezenVoertuigen($instructeurId);
    }



    function alleVoertuigen()
    {
        $alleVoertuigen = $this->instructeurModel->getAllVehicles();


        $tableRows = "";
        if (empty($alleVoertuigen)) {

            $tableRows = "<tr>
                            <td colspan='6'>
                                <div>Er zijn geen voertuigen beschikbaar op dit moment</div>
                            </td>
                          </tr>";
        } else {

            foreach ($alleVoertuigen as $voertuig) {


                $date_formatted = date_format(date_create($voertuig->Bouwjaar), 'd-m-Y');

                $tableRows .= "<tr>
                                    <td>$voertuig->TypeVoertuig</td>
                                    <td>$voertuig->Type</td>
                                    <td>$voertuig->Kenteken</td>
                                    <td>$date_formatted</td>
                                    <td>$voertuig->Brandstof</td>
                                    <td>$voertuig->Rijbewijscategorie</td>    
                                    <td>$voertuig->InstructeurNaam</td>  
                                    <td>
                                    <a href='" . URLROOT . "/instructeur/deleteVoertuigFromAll/$voertuig->Id'>
                                   <img src = '/public/img/b_drop.png'>
                                   </a> 
                                   </td> 
                            </tr>";
            }
        }

        $data = [
            'tableRows' => $tableRows,
            'title' => 'Alle voertuigen'
        ];

        $this->view('Instructeur/alleVoertuigen', $data);
    }

    function deleteVoertuigFromAll($voertuigId)
    {
        $this->instructeurModel->deleteVoertuigfromAll($voertuigId);

        $this->view('Instructeur/deleteMessage');
        
        header('Refresh:3; url=/Instructeur/alleVoertuigen');
    }

    function deleteMessage()
    {
        $this->view('Instructeur/deleteMessage');
    }
}
