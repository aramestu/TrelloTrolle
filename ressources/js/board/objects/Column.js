import {DropElementArea} from "../../DropElementArea.js";

class Column extends DropElementArea
{

    id;
    title;
    element;

    constructor(id, title, element, func)
    {
        super(element, func);
        this.id = id;
        this.title = title;
        this.element = element;
    }

}

export {Column}