import {DragElement} from "../DragElement.js";
import {DropElementArea} from "../DropElementArea.js";

const columnElements= document.querySelectorAll(".trello-main div.colonne.droppable");
const memberElements= document.querySelectorAll("div#listeParticipants > ul > li");
const participantCheck = document.querySelector("[data-dnd]");
const wip= document.querySelector("div#wip ul");

const columns = [];
const cards = [];
const members = [];

class Column extends DropElementArea
{

    title;

    constructor(title, element, func)
    {
        super(element, func);
        this.title = title;
    }

}

class Card extends DragElement
{

    column;
    participants;

    constructor(column, element)
    {
        super(element);
        this.column = column;
        this.participants = [];
    }

}

function updateWIP()
{
    let html = '';
    members.forEach(member =>
    {
        html += '<li>' + member + '<ul>'
        const infos = getInfos(member);
        Object.keys(infos).forEach(column =>
        {
            html += '<li>' + infos[column] + ' ' + column + '</li>'
        });
        html += '</ul></li>'
    });
    wip.innerHTML = html;
}

function getInfos(member)
{
    let infos = {};
    cards.forEach(card =>
    {
        if(card.participants.includes(member))
        {
            if(infos[card.column.title] === undefined)
            {
                infos[card.column.title] = 0;
            }
            infos[card.column.title]++;
        }
    });
    return infos;
}

function getFromElement(element)
{
    for(let cardObj of cards)
    {
        if(cardObj.element === element)
        {
            return cardObj;
        }

    }
    return null;
}

function initDragAndDrop()
{
    columnElements.forEach(columnElement =>
    {
        let title = columnElement.querySelector(".titre.icons_menu span").textContent;
        let column = new Column(title, columnElement, function (dragElement)
        {
            let corps = columnElement.querySelector("div.corps");
            corps.appendChild(dragElement);

            let card = getFromElement(dragElement);
            if(card !== null)
            {
                card.column = column;
            }

            updateWIP();
        });

        columns.push(column);

        const cardElements = columnElement.querySelectorAll(".trello-main div.carte");
        cardElements.forEach(cardElement =>
        {
            let card = new Card(column, cardElement);

            const assigns = cardElement.querySelectorAll(".pied span");
            assigns.forEach(assign =>
            {
                card.participants.push(assign.textContent)
            })
            cards.push(card);
        });
    });
    memberElements.forEach(memberElement =>
    {
        members.push(memberElement.textContent)
    });
}

let participant = participantCheck !== null && participantCheck.dataset.dnd === '1';
if(participant)
{
    initDragAndDrop();
}