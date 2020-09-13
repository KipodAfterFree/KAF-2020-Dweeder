let name = "Guest";

window.addEventListener("load", async function () {
    // Load modules
    await Module.import("UI");
    await Module.import("API");
    await Module.import("External:Authenticate");

    // Check whether login is needed
    if (window.localStorage.getItem("token")) {
        login();
    }
});

function login() {
    Authenticate.initialize("Dwidder").then((mName) => {
        name = mName;
        // Show logout button
        UI.hide("login");
        UI.show("logout");
    });
}

function logout() {
    // Remove token
    Authenticate.finalize();
    // Reload page
    window.location.reload();
}