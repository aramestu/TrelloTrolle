const flashContainer = document.querySelector("header div#flash-container");

function flashMessage(type, message)
{
    let div = document.createElement("div");
    div.classList.add('alert');
    div.classList.add(`alert-${type}`);
    div.textContent = message;
    flashContainer.appendChild(div);
}

export {flashMessage}