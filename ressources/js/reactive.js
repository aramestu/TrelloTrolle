export {applyAndRegister, reactive, startReactiveDom};

let objectByName = new Map();
let registeringEffect = null;
let objetDependencies = new Map();

function applyAndRegister(effect){
    registeringEffect = effect;
    effect();
    registeringEffect = null;
}

function reactive(passiveObject, name) {
    objetDependencies.set(passiveObject, new Map());
    const handler ={
        get(target, key){
            if(registeringEffect !== null)
                registerEffect(target, key);
            return target[key];
        },
        set(target, key, value){
            target[key] = value;
            trigger(target, key);
            return true;
        },
    };
    let reactiveObject = new Proxy (passiveObject, handler);
    objectByName.set(name, reactiveObject);
    return reactiveObject;
}

function startReactiveDom(subDOM=document) {
    for (let elementClickable of subDOM.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            objet[methode](argument);
        })
    }
    for (let inputElement of subDOM.querySelectorAll("[data-reactiveinput]")) {
        const [obj, prop] = inputElement.dataset.reactiveinput.split('.');
        inputElement.addEventListener('input', (event) => {
            objectByName.get(obj)[prop] = inputElement.value;
        })
    }
    for (let rel of subDOM.querySelectorAll("[data-textfun]")){
        const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
        applyAndRegister(()=>{rel.textContent = objectByName.get(obj)[fun](arg)});
    }
    for (let rel of subDOM.querySelectorAll("[data-textvar]")){
        const [obj, prop] = rel.dataset.textvar.split('.');
        applyAndRegister(()=>{rel.textContent = objectByName.get(obj)[prop]});
    }
    for (let rel of subDOM.querySelectorAll("[data-stylefun]")) {
        const [obj, fun, arg] = rel.dataset.stylefun.split(/[.()]+/);
        applyAndRegister(() => {Object.assign(rel.style, objectByName.get(obj)[fun](arg))});
    }
    for (let rel of subDOM.querySelectorAll("[data-srcvar]")){
        const [obj, prop] = rel.dataset.srcvar.split('.');
        applyAndRegister(()=>{rel.src = objectByName.get(obj)[prop]});
    }
    for (let rel of subDOM.querySelectorAll("[data-srcfun]")){
        const [obj, fun, arg] = rel.dataset.srcfun.split(/[.()]+/);
        applyAndRegister(()=>{rel.src = objectByName.get(obj)[fun](arg)});
    }
    for (let rel of subDOM.querySelectorAll("[data-htmlfun]")) {
        const [obj, fun, arg] = rel.dataset.htmlfun.split(/[.()]+/);
        applyAndRegister(() => {rel.innerHTML = objectByName.get(obj)[fun](arg);
            startReactiveDom(rel);});
    }
}

function trigger(target, key){
    if (objetDependencies.get(target).has(key) ){
        for(let effect of objetDependencies.get(target).get(key)){
            effect();
        }
    }
}

function registerEffect(target, key){
    if (!objetDependencies.get(target).has(key)) {
        objetDependencies.get(target).set(key, new Set());
    }
    objetDependencies.get(target).get(key).add(registeringEffect);
}