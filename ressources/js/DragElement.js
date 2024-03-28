export {DragElement};

class DragElement{
    constructor(element) {
        this.element = element;
        this.element.draggable = true;

        element.addEventListener("dragstart", this.dragStart.bind(this));
        element.addEventListener("dragend", this.dragEnd.bind(this));
        element.addEventListener("drag", this.drag.bind(this));
    }

    dragStart(event) {
        this.element.classList.add("currentDraggedElement");
        Object.assign(this.element.style, {opacity : "0.2"});
    }

    dragEnd(event) {
        Object.assign(this.element.style, {opacity: "1"});
        this.element.classList.remove("currentDraggedElement")
    }

    drag(event) {

    }
}
