import {DragElement} from "../DragElement.js";

const cards = {};

class Card extends DragElement
{

    id;
    column;
    participants;

    constructor(id, column, element)
    {
        super(element);
        this.id = id;
        this.column = column;
        this.participants = [];

        cards[id] = this;
    }

}

export {Card, cards}