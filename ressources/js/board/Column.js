import {DropElementArea} from "../DropElementArea.js";

const columns = [];

class Column extends DropElementArea
{

    id;
    title;
    element;

    constructor(id, title, element, func)
    {
        super(element, func);
        this.title = title;
        this.element = element;

        columns.push(this);
    }

}

export {Column, columns}