import {Column} from "./Column.js";
import {Card, cards} from "./Card.js";
import {openCardView} from "./cardView.js";

const columnElements= document.querySelectorAll(".trello-main div.colonne.droppable");
const memberElements= document.querySelectorAll("div#listeParticipants > ul > li");
const participantCheck = document.querySelector("[data-dnd]");
const wip= document.querySelector("div#wip ul");

const members = [];

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

function updateColumn(column, dragElement)
{
    let corps = column.element.querySelector("div.corps");
    corps.appendChild(dragElement);

    let card = getFromElement(dragElement);
    if(card !== null)
    {
        card.column = column;
    }

    updateWIP();
}

function initDragAndDrop()
{
    columnElements.forEach(columnElement =>
    {
        let title = columnElement.querySelector(".titre.icons_menu span").textContent;
        let column = new Column(title, columnElement, dragElement => updateColumn(column, dragElement));

        const cardElements = columnElement.querySelectorAll(".trello-main div.carte");
        cardElements.forEach(cardElement =>
        {
            let id = cardElement.dataset.id;
            let card = new Card(id, column, cardElement);

            cardElement.addEventListener("click", () => openCardView(id))

            const assigns = cardElement.querySelectorAll(".pied span");
            assigns.forEach(assign => card.participants.push(assign.textContent));
        });
    });
}

memberElements.forEach(memberElement => members.push(memberElement.textContent));

let participant = participantCheck !== null && participantCheck.dataset.dnd === '1';
if(participant)
{
    initDragAndDrop();
}

export {members}