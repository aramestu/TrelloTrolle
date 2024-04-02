const inputMembers = document.querySelector("div.member-selector > input#member-add");
const membersAC = document.querySelector("div.member-selector > div.autocompletion");

let memberRequest = null;
let timeout = null;
let hintText = null;

function emptySelections()
{
    while(membersAC.lastElementChild)
    {
        membersAC.removeChild(membersAC.lastElementChild);
    }
    membersAC.hidden = true;
}

// TODO Remove temporary functions
function showResults(tableau)
{
    membersAC.hidden = false;
    tableau.forEach(el =>
    {
        const opt = document.createElement("p");
        opt.textContent = el.nom + " " + el.prenom + " (" + el.login + ")";

        membersAC.appendChild(opt);
    });

}

function maRequeteAJAX(stringMember)
{
    requeteAJAX(stringMember, callback_4, startLoadingAction, endLoadingAction);
}

function requeteAJAX(stringMember, callback, startAction, endAction)
{
    let url = "php/requeteVille.php?ville=" + encodeURIComponent(stringMember);
    startAction();

    if(memberRequest !== null && memberRequest.readyState < 4)
    {
        memberRequest.abort();
    }

    memberRequest = new XMLHttpRequest();
    memberRequest.open("GET", url, true);
    memberRequest.addEventListener("load", function ()
    {
        try
        {
            callback(memberRequest);
            endAction();
        }
        catch (e)
        {
            showErrorText();
        }

    });
    memberRequest.addEventListener("error", function()
    {
        showErrorText();
    })
    memberRequest.send(null);
}

function showErrorText()
{
    hintText.textContent = "Une erreur s'est produite."
    hintText.style.color = "#b00000"
}

function startLoadingAction()
{
    membersAC.hidden = false;
    hintText = document.createElement("p");
    hintText.classList.add("search-label");
    hintText.textContent = "Recherche en cours...";
    membersAC.appendChild(hintText);
}

function endLoadingAction()
{
    membersAC.removeChild(membersAC.querySelector(".search-label"))
}

function callback_4(req)
{
    const villes = JSON.parse(req.responseText);
    const noms = [];

    villes.forEach(ville => noms.push(ville.name))
    showResults(noms);
}

inputMembers.addEventListener("input", function()
{
    emptySelections();
    if(inputMembers.value.length < 2 && memberRequest !== null)
    {
        memberRequest.abort();
        return;
    }

    clearTimeout(timeout);
    timeout = setTimeout(() => maRequeteAJAX(inputMembers.value), 200);
});