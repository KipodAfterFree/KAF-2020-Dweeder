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
            readDweed(parameters.get("dweed")).then((dweed) => {
                insertDweed(dweed, "big");
            }).catch(alert);
        } else {
            API.call("dwidder", "listDweed", {}).then((list) => {
                for (let id of list.slice(-10)) {
                    readDweed(id).then((dweed) => {
                        insertDweed(dweed, "small");
                    }).catch(alert);
                }
            }).catch(console.warn);
        }
    }
});

function insertDweed(dweed, style) {
    UI.find("dweeds").appendChild(UI.populate("dweed-" + style, dweed));
}

function newUser() {
    API.call("dwidder", "newUser", {
        name: UI.read("setup-name")
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
            id: id
        }).then((dweed) => {
            // Ensure dweed structure
            if (!dweed.hasOwnProperty("title"))
                reject("Missing title property");
            if (!dweed.hasOwnProperty("contents"))
                reject("Missing contents property");
            if (!dweed.hasOwnProperty("name"))
                reject("Missing name property");
            if (!dweed.hasOwnProperty("date"))
                reject("Missing date property");
            // Resolve
            resolve(dweed);
        }).catch(reject);
    });
}