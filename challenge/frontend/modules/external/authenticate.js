/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

class Authenticate {
    /**
     * Authenticates the user by requiring sign-up, sign-in and token validation.
     */
    static initialize(title = document.title) {
        return new Promise(async (resolve, reject) => {
            // Await module imports
            await Module.import("API");
            await Module.import("UI");

            // Preserve the HTML
            let contents = document.body.innerHTML;

            // Load the new HTML
            document.body.innerHTML = atob("PGRpdiByb3c+CiAgICA8ZGl2IHN0eWxlPSJtYXgtd2lkdGg6IDQ2MHB4OyIgY29sdW1uPgogICAgICAgIDwhLS0gVGl0bGVzIC0tPgogICAgICAgIDxwIGxhcmdlIGNlbnRlciBpZD0idGl0bGUiPldlbGNvbWUgYmFjayE8L3A+CiAgICAgICAgPHAgbWVkaXVtIGNlbnRlciBpZD0ib3V0cHV0Ij5QbGVhc2UgYXV0aGVudGljYXRlIHRvIGNvbnRpbnVlPC9wPgogICAgICAgIDwhLS0gVXNlciBpbnRlcmZhY2VzIC0tPgogICAgICAgIDxkaXYgaWQ9ImludGVyZmFjZSIgaGlkZGVuIGNvbHVtbj4KICAgICAgICAgICAgPCEtLSBJbnB1dHMgLS0+CiAgICAgICAgICAgIDxkaXYgaWQ9ImlucHV0cyIgY29sdW1uPgogICAgICAgICAgICAgICAgPGlucHV0IG1lZGl1bSBsZWZ0IGlkPSJuYW1lIiB0eXBlPSJ0ZXh0IiBwbGFjZWhvbGRlcj0iTmFtZSIvPgogICAgICAgICAgICAgICAgPGlucHV0IG1lZGl1bSBsZWZ0IGlkPSJwYXNzd29yZCIgdHlwZT0icGFzc3dvcmQiIHBsYWNlaG9sZGVyPSJQYXNzd29yZCIvPgogICAgICAgICAgICA8L2Rpdj4KICAgICAgICAgICAgPCEtLSBCdXR0b25zIC0tPgogICAgICAgICAgICA8ZGl2IGlkPSJidXR0b25zIiBjb2x1bW4+CiAgICAgICAgICAgICAgICA8ZGl2IHJvdz4KICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIG1lZGl1bSBjZW50ZXIgaWQ9InNpZ25VcCI+U2lnbiBVcDwvYnV0dG9uPgogICAgICAgICAgICAgICAgICAgIDxidXR0b24gbWVkaXVtIGNlbnRlciBpZD0ic2lnbkluIj5TaWduIEluPC9idXR0b24+CiAgICAgICAgICAgICAgICA8L2Rpdj4KICAgICAgICAgICAgICAgIDxkaXYgaGlkZGVuIGNvbHVtbj4KICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIG1lZGl1bSBjZW50ZXIgaWQ9InZhbGlkYXRlIj5WYWxpZGF0ZTwvYnV0dG9uPgogICAgICAgICAgICAgICAgPC9kaXY+CiAgICAgICAgICAgIDwvZGl2PgogICAgICAgIDwvZGl2PgogICAgPC9kaXY+CjwvZGl2Pg==");

            // Store elements
            let titleElement = UI.find("title");
            let outputElement = UI.find("output");

            // Update the title
            titleElement.innerText = "Welcome to " + title;

            // Register event listeners
            UI.find("signUp").addEventListener("click", (event) => {
                // Hide the UI
                UI.hide("interface");

                // Display progress
                outputElement.innerText = "Hold on - Signing up...";
                outputElement.style.setProperty("color", null);

                // Try to sign up
                API.call("authenticate".toLowerCase(), "signUp", {
                    name: UI.find("name").value,
                    password: UI.find("password").value
                }).then((value) => {
                    // Click the signIn button
                    UI.find("signIn").click();
                }).catch((reason) => {
                    // Display reason
                    outputElement.innerText = reason;
                    outputElement.style.setProperty("color", "red");

                    // Show the UI
                    UI.show("interface");
                });
            });

            UI.find("signIn").addEventListener("click", (event) => {
                // Hide the UI
                UI.hide("interface");

                // Display progress
                outputElement.innerText = "Hold on - Signing in...";
                outputElement.style.setProperty("color", null);
                // Try to sign in
                API.call("authenticate".toLowerCase(), "signIn", {
                    name: UI.find("name").value,
                    password: UI.find("password").value
                }).then((token) => {
                    // Store the token
                    this.token(token);

                    // Click the validate button
                    UI.find("validate").click();
                }).catch((reason) => {
                    // Display reason
                    outputElement.innerText = reason;
                    outputElement.style.setProperty("color", "red");

                    // Show the UI
                    UI.show("interface");
                });
            });

            UI.find("validate").addEventListener("click", (event) => {
                // Hide the UI
                UI.hide("interface");

                // Display progress
                outputElement.innerText = "Hold on - Validating...";
                outputElement.style.setProperty("color", null);

                // Try to validate
                API.call("authenticate".toLowerCase(), "validate", {
                    token: this.token()
                }).then((userID) => {
                    // Restore the HTML
                    document.body.innerHTML = contents;

                    // Resolve
                    resolve(userID);
                }).catch((reason) => {
                    // Display reason
                    outputElement.innerText = reason;
                    outputElement.style.setProperty("color", "red");

                    // Show the UI
                    UI.show("interface");
                });
            });

            // Automatic validation
            if (this.token() !== null) {
                // Click the validate button
                UI.find("validate").click();
            } else {
                // Show the UI
                UI.show("interface");
            }
        });
    }

    /**
     * Returns the authentication token.
     */
    static token(token = null) {
        // Check the token
        if (token !== null)
            // Set the token
            localStorage.setItem("token", token);
        // Return the token
        return localStorage.getItem("token");
    }

    /**
     * Signs the user out.
     */
    static finalize() {
        // Remove from localStorage
        localStorage.removeItem("token");
    }
}