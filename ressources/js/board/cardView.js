import {flashMessage} from "./utils.js";
import {board} from "./dynamicBoard.js";

const contentTemplate = document.querySelector("template.card-view-content");
const cardView = document.querySelector("div.card-view-background");

const cardFrame = cardView.querySelector("div.card-frame");
const loading = cardView.querySelector("div.loading");

let opened = false;

async function openCardView(card)
{
    if(opened)
    {
        return;
    }

    try
    {
        showView();

        let cardRes = await fetch(`${urlBase}/api/cartes/${card.id}`, {method: "GET"});

        loading.classList.add("hidden");

        if(!cardRes.ok)
        {
            let errorMsg = document.createElement("p");
            errorMsg.textContent = "Une erreur est survenue lors du chargement de la carte. Veuillez réessayer plus tard.";

            cardView.appendChild(errorMsg);
            return;
        }

        let cardJson = await cardRes.json();
        createContent(card, cardJson)
    }
    catch (e)
    {
        console.log(e);
    }

}

function showView()
{
    opened = true;

    cardView.classList.remove("hidden");
}

function createContent(cardObj, result)
{
    let clone = contentTemplate.content.cloneNode(true);
    cardFrame.appendChild(clone);

    let content = cardFrame.querySelector("div.view-content");
    let card = result['carte'];

    let titreCarte = content.querySelector("#titreCarte");
    titreCarte.value = card.titreCarte;

    let descriptifCarte = content.querySelector("#descriptifCarte");
    descriptifCarte.value = card.descriptifCarte;

    let couleurCarte = content.querySelector("#couleurCarte");
    couleurCarte.value = card.couleurCarte;

    let affectationSelect = content.querySelector("#affectationsCarte");
    for(let member of board.members)
    {
        let option = document.createElement("option");
        option.value = member.login;
        option.textContent = member.prenom + ' ' + member.nom + ' (' + member.login + ')';

        affectationSelect.appendChild(option);
    }

    let updateButton = content.querySelector('#update');
    updateButton.addEventListener("click", async function()
    {
        let title = titreCarte.value;
        let desc = descriptifCarte.value;
        let color = couleurCarte.value;
        let affectations = getOptions(affectationSelect);

        let res = await fetch(urlBase + `/api/cartes`,
            {
                method: "PATCH",
                body: JSON.stringify({
                    idCarte: card.idCarte,
                    titreCarte: title,
                    descriptifCarte: desc,
                    couleurCarte: color,
                    affectationsCarte: affectations,
                    idColonne: card.idColonne
                })
            });

        closeCardView();

        if(res.ok)
        {
            cardObj.title = title;
            cardObj.description = desc;
            cardObj.color = color;
            cardObj.participants = affectations;
            flashMessage('success', 'Mise à jour du tableau réalisée avec succès !');
            return;
        }

        let message = await res.text();
        flashMessage('danger', `Une erreur est survenue lors de la mise à jour de la carte : ${message}`);
    });

    if (!isParticipant)
    {
        titreCarte.disabled = true;
        descriptifCarte.disabled = true;
        couleurCarte.disabled = true;
        affectationSelect.disabled = true;

        content.removeChild(updateButton);
    }

}

function getOptions(select)
{
    let opts = [];
    for(let option of select.options)
    {
        if(option.selected)
        {
            opts.push(option.value);
        }

    }
    return opts;
}

function closeCardView()
{
    if(!opened)
    {
        return;
    }

    while(cardFrame.lastElementChild)
    {
        cardFrame.removeChild(cardFrame.lastElementChild);
    }

    loading.classList.remove("hidden");
    cardView.classList.add("hidden");
    opened = false;
}

cardView.addEventListener("click", evt =>
{
    if(evt.target !== cardView && evt.target.id !== "close-card-view")
    {
        return;
    }
    closeCardView();
});

export {openCardView, closeCardView}