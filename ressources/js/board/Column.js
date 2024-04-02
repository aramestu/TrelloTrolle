import {DropElementArea} from "../DropElementArea.js";

const columns = [];

class Column extends DropElementArea
{

    title;

    constructor(title, element, func)
    {
        super(element, func);
        this.title = title;

        columns.push(this)
    }

}

export {Column, columns}