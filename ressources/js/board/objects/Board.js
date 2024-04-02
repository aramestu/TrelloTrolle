class Board
{

    id;
    title;
    owner;
    members;
    columns;
    cards;

    constructor(id, title, owner, members, columns, cards)
    {
        this.id = id;
        this.title = title;
        this.owner = owner;
        this.members = members;
        this.columns = columns;
        this.cards = cards;
    }

}

export {Board}