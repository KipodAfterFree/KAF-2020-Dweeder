function load() {
    // Load some modules
    Module.load("UI", "API", "Authenticate", "Global:Popup").then(() => {
        Authenticate.initialize().then(() => {
            UI.hide("loading");
            UI.show("home");
        });
    });
}