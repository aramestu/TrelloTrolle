export {DragElement};

class DragElement{
    constructor(element) {
        this.element = element;
        this.element.draggable = true;
        this.isActive = true;
        element.addEventListener("dragstart", this.dragStart.bind(this));
        element.addEventListener("dragend", this.dragEnd.bind(this));
        element.addEventListener("drag", this.drag.bind(this));
    }

    setActive(state){
        this.isActive = state;
        this.element.draggable = state;
    }

    dragStart(event) {
        if(this.isActive){
            this.element.classList.add("currentDraggedElement");
            Object.assign(this.element.style, {opacity : "0.6"});
        }
    }

    dragEnd(event) {
        if(this.isActive){
            Object.assign(this.element.style, {opacity: "1"});
            this.element.classList.remove("currentDraggedElement")
        }
    }

    drag(event) {

    }
}
