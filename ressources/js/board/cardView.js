import {members} from "./dynamicBoard.js";

const overlayBlock = document.querySelector("div.trello-overlay");
const contentTemplate = document.querySelector("template.card-view-content");
const cardView = document.querySelector("div.card-view-background");
const loading = cardView.querySelector("div.loading");

const ROOT_URL = '/TrelloTrolle/web';

let opened = false;

async function openCardView(cardID)
{
    if(opened)
    {
        return;
    }

    try
    {
        showView();

        let cardRes = await fetch(`${ROOT_URL}/api/cartes/${cardID}`, {method: "GET"});

        loading.classList.add("hidden");

        if(!cardRes.ok)
        {
            let errorMsg = document.createElement("p");
            errorMsg.textContent = "Une erreur est survenue lors du chargement de la carte. Veuillez réessayer plus tard.";

            cardView.appendChild(errorMsg);
            return;
        }

        let cardJson = await cardRes.json();
        createContent(cardJson)
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

function createContent(result)
{
    let clone = contentTemplate.content.cloneNode(true);
    cardView.appendChild(clone);

    let content = overlayBlock.querySelector("div.view-content");
    let card = result['carte'];

    content.querySelector("#titreCarte").value = card.titreCarte;
    content.querySelector("#descriptifCarte").value = card.descriptifCarte;
    content.querySelector("#couleurCarte").value = card.couleurCarte;

    let affectationSelect = content.querySelector("#affectationsCarte");
    for(let member of members)
    {
        let option = document.createElement("option");
        option.value = member;

        affectationSelect.appendChild(member);
    }

}

function closeCardView()
{
    if(!opened)
    {
        return;
    }

    while(cardView.lastElementChild)
    {
        cardView.removeChild(cardView.lastElementChild);
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