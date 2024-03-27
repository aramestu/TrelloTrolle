<?php

use App\Trellotrolle\Lib\ConnexionUtilisateur;

?>

<div><p>Bienvenue sur <strong>Trello-Trollé</strong>!</p></div>
<div>
    <?php if (ConnexionUtilisateur::estConnecte()) { ?>
        <span><a href="controleurFrontal.php?action=afficherListeMesTableaux&controleur=tableau">Consulter mes tableaux</a></span>
    <?php } else { ?>
        <p>
            Pour créer des tableaux, commencez par vous
            <a href="controleurFrontal.php?action=afficherFormulaireConnexion&controleur=utilisateur">connecter</a>
            ou par <a href="controleurFrontal.php?action=afficherFormulaireCreation&controleur=utilisateur">créer un
                compte</a>!
        </p>
    <?php } ?>
</div>
