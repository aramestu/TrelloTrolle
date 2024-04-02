import {DragElement} from "../../DragElement.js";

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
    }

    getParticipants(){
        let result = "";
        this.participants.forEach(participant =>{
            result += `<span>${participant.prenom} ${participant.nom}</span>`;
        });
        console.log(result);
        return result;
    }

}

export {Card}