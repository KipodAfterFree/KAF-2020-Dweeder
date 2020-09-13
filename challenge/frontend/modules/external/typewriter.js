/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/Modules/
 **/

class Typewriter {
    /**
     * Animates typing on views.
     * @param view View
     * @return Promise
     */
    static animate(view) {
        // Create a promise
        return new Promise(async (resolve, reject) => {
            // Await module import
            await Module.import("UI");

            // Find the view
            view = UI.find(view);

            // Load the final text
            let text = view.innerHTML;

            // Clear the text
            view.innerHTML = null;

            // Create a function
            let type = function () {
                if (view.innerHTML.length !== text.length) {
                    let character = text[view.innerHTML.length];
                    // Append the character
                    view.innerHTML += character;

                    // Calculate time
                    let time = 100;

                    // Special characters
                    if (character === ",")
                        time = 200;

                    // Schedule the next loop
                    setTimeout(type, time);
                } else {
                    resolve();
                }
            };

            // Start typing
            setTimeout(type, 200);
        });
    }
}