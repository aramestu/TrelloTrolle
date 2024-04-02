import {DragElement} from "../DragElement.js";

let cards = {};

class Card extends DragElement
{

    id;
    title;
    description;
    color;
    column;
    participants;

    constructor(id, title, description, color, participants, column, element)
    {
        super(element);
        this.id = id;
        this.title = title;
        this.description = description;
        this.color = color;
        this.participants = participants;
        this.column = column;

        cards[id] = this;
    }

}

export {Card, cards}