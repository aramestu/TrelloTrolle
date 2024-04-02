const overlayBlock = document.querySelector("div.trello-overlay");
const cardViewTemplate = document.querySelector("template.card-view-overlay");

let view = null;
let opened = false;

function openCardView(cardID)
{
    if(opened)
    {
        return;
    }

    createView(cardID);
}

function createView(result)
{
    opened = true;

    let clone = cardViewTemplate.content.cloneNode(true);
    overlayBlock.appendChild(clone);

    view = overlayBlock.querySelector("div.card-view-background");
    view.addEventListener("click", function (event)
    {
        if(event.target !== view)
        {
            return;
        }
        closeCardView();
    });

}

function closeCardView()
{
    if(!opened)
    {
        return;
    }

    opened = false;
    overlayBlock.removeChild(view);
}