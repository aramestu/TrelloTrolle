import {Column} from "./Column.js";
import {Card, cards} from "./Card.js";
import {openCardView} from "./cardView.js";
import {flashMessage} from "./utils.js";
import {reactive, startReactiveDom} from "../reactive.js";

const columnElements = document.querySelectorAll(".trello-main div.colonne.droppable");
const memberElements = document.querySelectorAll("div#listeParticipants > ul > li");
const wip = document.querySelector("div#wip ul");

/*function updateWIP()
{
    let html = '';
    members.forEach(member =>
    {
        html += '<li>' + member + '<ul>'
        const infos = getInfos(member);
        Object.keys(infos).forEach(column => html += '<li>' + infos[column] + ' ' + column + '</li>');
        html += '</ul></li>'
    });
    wip.innerHTML = html;
}*/

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

async function updateColumn(column, dragElement)
{
    let card = getFromElement(dragElement);
    if(card === null)
    {
        return;
    }

    let res = await fetch(`${urlBase}/api/cartes`, {
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
    }

    card.column = column;

    //TODO Remplacer par du réactif
    let corps = column.element.querySelector("div.corps");
    corps.appendChild(dragElement);
    //updateWIP();
}

function initDragAndDrop()
{
    columnElements.forEach(columnElement =>
    {
        let title = columnElement.querySelector(".titre.icons_menu span").textContent;
        let id = columnElement.dataset.id;

        let column = new Column(id, title, columnElement, dragElement => updateColumn(column, dragElement));

        const cardElements = columnElement.querySelectorAll(".trello-main div.carte");
        cardElements.forEach(cardElement =>
        {
            let id = cardElement.dataset.id;
            let card = new Card(id, column, cardElement);
            card.setActive(isParticipant);
            setReactiveCard(id, cardElement);

            cardElement.addEventListener("click", () => openCardView(id))

            const assigns = cardElement.querySelectorAll(".pied span");
            assigns.forEach(assign => card.participants.push(assign.textContent));
        });
    });
}

function setReactiveCard(id, card, cardElement){
    let title = cardElement.querySelector(".titre span");
    let description = cardElement.querySelector(".corps");
    let affectations  = cardElement.querySelector("pied");

    let nomCarte = "Carte" + id;

    title.setAttribute("data-textvar", `${nomCarte}.id`);
    description.setAttribute("data-textvar", `${nomCarte}.description`);
    affectations.setAttribute("data-htmlfun", `${nomCarte}.getAssociations`);
    reactive(card, nomCarte)
}

initDragAndDrop();
startReactiveDom();