export {DropElementArea}

class DropElementArea{
    constructor(element, fonction) {
        this.fonction = fonction;

        element.addEventListener('dragover', function(event) {
            // Empêcher le comportement par défaut
            event.preventDefault();
        });

        element.addEventListener('drop', this.drop.bind(this));
    }

    drop(event){
        // Empêcher le comportement par défaut
        event.preventDefault();

        // Récupération de l'élément
        let dragElement = document.getElementsByClassName('currentDraggedElement')[0];

        // Execution de la fonction donnée en paramètre
        this.fonction(dragElement);
    }

}