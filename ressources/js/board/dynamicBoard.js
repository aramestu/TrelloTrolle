import {Board} from "./objects/Board.js";
import {Column} from "./objects/Column.js";
import {Card} from "./objects/Card.js";
import {reactive, startReactiveDom} from "../reactive.js";
import {openCardView} from "./cardView.js";

const columnTemplate = document.querySelector("template#column-template");
const cardTemplate = document.querySelector("template#card-template");
const boardBody = document.querySelector(".tableau div.corps")

const board = await loadBoard(boardCode)

startReactiveDom();

async function loadBoard(boardCode)
{
    let boardRes = await fetch(`${urlBase}/api/tableaux/${boardCode}`);
    let boardJson = await boardRes.json();

    let boardInfo = boardJson['tableau'];
    let associations = boardJson['associationColonneCarte'];

    let columns = [];
    let cards = [];

    for (let columnInfo of associations['colonnes'])
    {
        let column = createColumn(columnInfo);
        columns.push(column);

        for (let cardInfo of associations['associations'][column.id])
        {
            cards.push(createCard(column, cardInfo))
        }

    }

    return new Board(boardInfo.idTableau, boardInfo.titreTableau, boardInfo.proprietaireTableau,
        boardInfo.participants, columns, cards);
}

function createColumn(columnInfo)
{
    let columnId = columnInfo.idColonne;

    let clone = columnTemplate.content.cloneNode(true);
    let div = clone.querySelector("div.colonne.droppable");
    let columnName = "column-" + columnId;
    div.id =  columnName;

    boardBody.appendChild(clone);

    let title = div.querySelector(".titre.icons_menu span")
    title.setAttribute("data-textvar", `${columnName}.title`);
    title.textContent = columnInfo.titreColonne;

    let column = new Column(columnId, columnInfo.titreColonne, div, dragElement => updateColumn(column, dragElement));
    return reactive(column, columnName);
}

function createCard(column, cardInfo)
{
    let clone = cardTemplate.content.cloneNode(true);
    let div = clone.querySelector("div.carte");
    div.style.backgroundColor = cardInfo.couleurCarte;
    let cardName = "card-" + cardInfo.idCarte;
    div.id = cardName;

    let columnBody = column.element.querySelector("div.corps");
    columnBody.addEventListener('click', () => openCardView(cardInfo.idCarte));
    columnBody.appendChild(clone);

    let title = div.querySelector(".titre.icons_menu span")
    title.setAttribute("data-textvar", `${cardName}.title`);
    title.textContent = cardInfo.titreCarte;

    let body = div.querySelector(".corps");
    body.setAttribute("data-textvar", `${cardName}.description`);
    body.textContent = cardInfo.descriptifCarte;

    let foot = div.querySelector(".pied")
    foot.setAttribute("data-htmlfun", `${cardName}.getParticipants()`);
    for(let user of cardInfo.affectationsCarte)
    {
        foot.appendChild(createUserLabel(user))
    }

    return reactive(new Card(cardInfo.idCarte, cardInfo.titreCarte, cardInfo.descriptifCarte, cardInfo.couleurCarte,
        cardInfo.affectationsCarte, column, div), cardName);
}

function createUserLabel(user)
{
    let span = document.createElement("span");
    span.textContent = user.prenom + ' ' + user.nom;
    return span;
}

function getFromElement(element)
{
    for(let cardObj of board.cards)
    {
        if(cardObj.element === element)
        {
            return cardObj;
        }

    }
    return null;
}

async function updateColumn(column, dragElement)
{
    let card = getFromElement(dragElement);
    if(card === null)
    {
        return;
    }

    /*let res = await fetch(`${urlBase}/api/cartes`, {
        method: "PATCH",
        body: JSON.stringify({
            idCarte: card.id,
            titreCarte: titreCarte.value,
            descriptifCarte: descriptifCarte.value,
            couleurCarte: couleurCarte.value,
            affectationsCarte: getOptions(affectationSelect),
            idColonne: card.idColonne
        })
    });

    if(!res.ok)
    {
        let message = await res.text();
        flashMessage('danger', `Une erreur est survenue lors du déplacement de la carte : ${message}`);
        return;
    }*/

    card.column = column;

    //TODO Remplacer par du réactif
    let body = column.element.querySelector("div.corps");
    body.appendChild(dragElement);
    //updateWIP();
}

export {board}
