export {DragAndDropElement}
class DragAndDropElement {

    constructor(element) {
        this.isMouseDown = false;
        this.element = element;
        this.initialPosX = -1;
        this.initialPosY = -1;


        element.addEventListener("mousedown", this.mouseDown.bind(this));
        element.addEventListener("mouseup", this.mouseUp.bind(this));
        element.addEventListener("mousemove", this.updatePosition.bind(this));
        element.addEventListener("mouseleave", this.updatePosition.bind(this));
    }

    mouseDown(event){
        this.isMouseDown = true;
        const rect = this.element.getBoundingClientRect();
        console.log("topElement: " + rect.top + " souris X : " + event.clientY);
        console.log("leftElement: " + rect.left + " souris Y : " + event.clientX);
        this.initialPosX = rect.left - event.clientX;
        this.initialPosY = rect.top - event.clientY;
        console.log("mouse down");
    }

    mouseUp(event){
        this.isMouseDown = false;
        this.initialPosX = -1;
        this.initialPosY = -1;
        console.log("mouse up");
    }

    updatePosition(event){
        if(this.isMouseDown){
            console.log("bouge");
            Object.assign(this.element.style, {position: 'fixed', top: `${event.clientY + this.initialPosY}px`, left: `${event.clientX + this.initialPosX}px`})
        }else{
            Object.assign(this.element.style, {});
        }
    }
}