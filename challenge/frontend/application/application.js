window.addEventListener("load", async function () {
    // Load modules
    await Module.import("UI");
    await Module.import("API");

    // Check whether login is needed
    if (!window.localStorage.getItem("token")) {
        UI.view("setup");
    } else {
        let parameters = new URLSearchParams(window.location.search);
        if (parameters.has("dweed")) {
            loadDweed(parameters.get("dweed"));
        } else {
            loadDweeds();
        }
    }
});

function newUser() {
    API.call("dweeder", "newUser", {
        name: UI.read("setup-name"),
        handle: UI.read("setup-handle"),
    }).then((token) => {
        window.localStorage.setItem("token", token.toString());
        window.location.reload();
    }).catch(alert);
}

function insertDweed(dweed) {
    // Find the template
    let template = UI.find("dweed-" + dweed.display);
    if (template === null)
        template = UI.find("dweed-normal");
    // Make sure id is valid
    if (dweed.id.length > 28)
        return;
    for (let char of dweed.id)
        if ("0123456789 abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ !@#$%^&*()_-+={}|".includes(char) === false)
            return;
    // Add the dweed
    UI.find("dweeds").appendChild(UI.populate(template, dweed));
}

function writeDweed() {
    API.call("dweeder", "writeDweed", {
        token: window.localStorage.getItem("token"),
        title: UI.read("write-title"),
        contents: UI.read("write-contents")
    }).then((id) => {
        window.location = "?dweed=" + id;
    }).catch(alert);
}

function readDweed(id) {
    return new Promise((resolve, reject) => {
        API.call("dweeder", "readDweed", {
            token: window.localStorage.getItem("token"),
            id: id
        }).then((dweed) => {
            // Ensure dweed structure
            if (!dweed.hasOwnProperty("title"))
                reject("Missing title property");
            if (!dweed.hasOwnProperty("contents"))
                reject("Missing contents property");
            if (!dweed.hasOwnProperty("handle"))
                reject("Missing handle property");
            if (!dweed.hasOwnProperty("time"))
                reject("Missing date property");
            // Resolve
            resolve(dweed);
        }).catch(reject);
    });
}

function loadDweed(id) {
    UI.clear("dweeds");
    readDweed(id).then((dweed) => {
        insertDweed({
            id: id,
            ...dweed,
            display: "normal"
        });
    }).catch(alert);
}

function loadDweeds() {
    UI.clear("dweeds");
    API.call("dweeder", "listDweed", {
        token: window.localStorage.getItem("token")
    }).then((list) => {
        for (let id of list.slice(-10)) {
            readDweed(id).then((dweed) => {
                insertDweed({
                    ...dweed,
                    display: "normal",
                    id: id
                });
            }).catch(alert);
        }
    }).catch(console.warn);
}

function loadMentions() {
    UI.clear("dweeds");
    API.call("dweeder", "listMentions", {
        token: window.localStorage.getItem("token")
    }).then((list) => {
        for (let id of list) {
            readDweed(id).then((dweed) => {
                // Insert dweed
                insertDweed({
                    display: "normal",
                    ...dweed,
                    id: id
                });
            }).catch(alert);
        }
    }).catch(console.warn);
}