/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

class Authenticate {
    /**
     * Authenticates the user by requiring sign-up, sign-in and token validation.
     */
    static initialize() {
        return new Promise((resolve, reject) => {
            // Load the page's contents
            let contents = document.body.innerHTML;
            // Load the template
            Module.resource(Authenticate.name, "authenticate.html").then((html) => {
                // Clear the body
                UI.clear(document.body);
                // Append a populated template
                document.body.appendChild(UI.populate(html, {
                    title: document.title
                }));
                // Check token initialization
                if (Authenticate.getToken() !== null) {
                    // Hide the inputs
                    UI.hide("inputs");
                    // Change the output message
                    Authenticate.output("Hold on - Authenticating...");
                    // Send the API call
                    API.call(Authenticate.name.toLowerCase(), "validate", {
                        token: Authenticate.getToken()
                    }).then(result => {
                        // Change the page's contents
                        document.body.innerHTML = contents;
                        // Resolve
                        resolve(result);
                    }).catch(result => {
                        // Show the inputs
                        UI.show("inputs");
                        // Change the output message
                        Authenticate.output(result, true);
                        // Reject
                        reject(result);
                    });
                }
            });
        });
    }

    /**
     * Sends a signUp API call and handles the results.
     */
    static signUp() {
        return new Promise((resolve, reject) => {
            // Hide the inputs
            UI.hide("inputs");
            // Change the output message
            Authenticate.output("Hold on - Signing you up...");
            // Send the API call
            API.call(Authenticate.name.toLowerCase(), "signUp", {
                name: UI.find("name").value,
                password: UI.find("password").value
            }).then(result => {
                // Call the signIn function
                Authenticate.signIn().then(resolve).catch(reject);
            }).catch(result => {
                // Show the inputs
                UI.show("inputs");
                // Change the output message
                Authenticate.output(result, true);
                // Reject
                reject(result);
            });
        });
    }

    /**
     * Sends a signIn API call and handles the results.
     */
    static signIn() {
        return new Promise((resolve, reject) => {
            // Hide the inputs
            UI.hide("inputs");
            // Change the output message
            Authenticate.output("Hold on - Signing you in...");
            // Send the API call
            API.call(Authenticate.name.toLowerCase(), "signIn", {
                name: UI.find("name").value,
                password: UI.find("password").value
            }).then(result => {
                // Push the token
                Authenticate.setToken(result);
                // Resolve
                resolve();
            }).catch(result => {
                // Show the inputs
                UI.show("inputs");
                // Change the output message
                Authenticate.output(result, true);
                // Reject
                reject(result);
            });
        });
    }

    /**
     * Signs the user out.
     */
    static signOut() {
        // Remove from localStorage
        localStorage.removeItem(Authenticate.name.toLowerCase());
    }

    /**
     * Gets the authentication token.
     */
    static getToken() {
        return localStorage.getItem(Authenticate.name.toLowerCase());
    }

    /**
     * Sets the authentication token.
     */
    static setToken(token) {
        localStorage.setItem(Authenticate.name.toLowerCase(), token);
    }

    /**
     * Changes the output message.
     * @param text Output message
     * @param error Is the message an error?
     */
    static output(text, error = false) {
        // Store the output view
        let output = UI.find("output");
        // Set the output message
        output.innerText = text;
        // Check if the message is an error
        if (error) {
            // Set the text color to red
            output.style.setProperty("color", "red");
        } else {
            // Clear the text color
            output.style.removeProperty("color");
        }
    }
}