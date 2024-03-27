class ContainerDragAndDropElement{
    constructor(element) {
        this.element = element;
        this.selected = false;

        this.element.addEventListener('mouseleave', function(event) {
            this.selected = false;
            this.element.selected = false;
        });

        this.element.addEventListener('mouseenter', function(event) {
            this.selected = true;
            this.element.selected = true;
        });
    }

    selectElement(){

    }
}