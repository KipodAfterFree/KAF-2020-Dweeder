let name = "Guest";

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
            UI.clear("dweeds");
            readDweed(parameters.get("dweed")).then((dweed) => {
                insertDweed(dweed, "big", "dweeds");
            }).catch(alert);
        } else {
            loadDweeds();
        }
    }
});

function insertDweed(dweed, style, container) {
    UI.find(container).appendChild(UI.populate("dweed-" + style, dweed));
}

function newUser() {
    API.call("dwidder", "newUser", {
        name: UI.read("setup-name"),
        handle: UI.read("setup-handle"),
    }).then((token) => {
        window.localStorage.setItem("token", token.toString());
        window.location.reload();
    }).catch(alert);
}

function writeDweed() {
    API.call("dwidder", "writeDweed", {
        token: window.localStorage.getItem("token"),
        title: UI.read("write-title"),
        contents: UI.read("write-contents")
    }).then((id) => {
        window.location = "?dweed=" + id;
    }).catch(alert);
}

function readDweed(id) {
    return new Promise((resolve, reject) => {
        API.call("dwidder", "readDweed", {
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
            if (!dweed.hasOwnProperty("date"))
                reject("Missing date property");
            // Resolve
            resolve(dweed);
        }).catch(reject);
    });
}

function loadDweeds() {
    UI.clear("dweeds");
    API.call("dwidder", "listDweed", {
        token: window.localStorage.getItem("token")
    }).then((list) => {
        for (let id of list.slice(-10)) {
            readDweed(id).then((dweed) => {
                insertDweed(dweed, "small", "dweeds");
            }).catch(alert);
        }
    }).catch(console.warn);
}

function loadMentions() {
    UI.clear("mentions");
    API.call("dwidder", "listMentions", {
        token: window.localStorage.getItem("token")
    }).then((list) => {
        readDweed(id).then((dweed) => {
            // Modify dweed
            dweed.id = id;
            // Insert dweed
            insertDweed(dweed, "tiny", "mentions");
        }).catch(alert);
    }).catch(console.warn);
}