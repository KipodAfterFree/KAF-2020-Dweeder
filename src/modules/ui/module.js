/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

// Register a popstate listener to restore states.
window.addEventListener("popstate", (event) => {
    // Change contents
    History.restore(event.state);
});

class History {
    /**
     * Creates a preservable state for all elements in the page.
     */
    static preserve() {
        // Initialize map
        let state = [];
        // Find all elements
        let elements = document.getElementsByTagName("*");
        // Loop over all elements
        for (let element of elements) {
            // Make sure the element has an ID
            if (element.id.length === 0) {
                element.id = Math.floor(Math.random() * 1000000).toString();
            }
            // Add to object
            state.push([element.id, element.hasAttribute("hidden")]);
        }
        // Return map
        return state;
    }

    /**
     * Restores a preserved state for elements in the page.
     * @param state State
     */
    static restore(state = []){
        // Loop over map
        for (let [id, value] of state) {
            if (value) {
                UI.hide(id);
            } else {
                UI.show(id);
            }
        }
    }
}

class UI {
    /**
     * Returns a view by its ID or by it's own value.
     * @param v View
     * @returns {HTMLElement} View
     */
    static find(v) {
        if (typeof "" === typeof v || typeof '' === typeof v) {
            // ID lookup
            if (document.getElementById(v) !== undefined) {
                return document.getElementById(v);
            }
            // Query lookup
            if (document.querySelector(v) !== undefined) {
                return document.querySelector(v);
            }
        }
        // Return the input
        return v;
    }

    /**
     * Hides a given view.
     * @param v View
     */
    static hide(v) {
        // Set style to none
        UI.find(v).setAttribute("hidden", "true");
    }

    /**
     * Shows a given view.
     * @param v View
     */
    static show(v) {
        // Set style to original value
        UI.find(v).removeAttribute("hidden");
    }

    /**
     * Removes all children of a given view.
     * @param v View
     */
    static clear(v) {
        // Store view
        let view = UI.find(v);
        // Remove all views
        view.innerHTML = "";
    }

    /**
     * Removes a given view.
     * @param v View
     */
    static remove(v) {
        // Find view
        let element = UI.find(v);
        // Remove
        element.parentNode.removeChild(element);
    }

    /**
     * Changes a view's visibility.
     * @param v View
     */
    static view(v) {
        // Add history
        window.history.replaceState(History.preserve(), document.title);
        // Change views
        for (let view of Array.from(arguments)) {
            // Store view
            let element = UI.find(view);
            // Store parent
            let parent = element.parentNode;
            // Hide all
            for (let child of parent.children) {
                UI.hide(child);
            }
            // Show view
            UI.show(element);
        }
        // Add history
        window.history.pushState(History.preserve(), document.title);
    }

    /**
     * Populates a template.
     * @param template Template
     * @param parameters Parameters
     * @return HTMLElement
     */
    static populate(template, parameters = {}) {
        // Find the template
        let templateElement = UI.find(template);
        // If the element is null, create one
        if (templateElement === null) {
            templateElement = document.createElement("template");
            // Fill the contents
            templateElement.innerHTML = template;
        }
        // Create the cloned node
        let created = templateElement.cloneNode(true);
        // Modify the HTML
        let html = created.innerHTML;
        // Replace parameters
        for (let key in parameters) {
            if (key in parameters) {
                let search = "${" + key + "}";
                let value = parameters[key];
                // Sanitize value
                let sanitizer = document.createElement("p");
                sanitizer.innerText = value;
                value = sanitizer.innerHTML;
                // Replace
                while (html.includes(search))
                    html = html.replace(search, value);
            }
        }
        // Replace the HTML
        created.innerHTML = html;
        // Return created element
        return created.content;
    }
}