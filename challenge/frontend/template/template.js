/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

class Module {

    /**
     * Imports a module.
     * @param module Module
     */
    static import(module) {
        return new Promise((resolve, reject) => {
            // Transform module name
            module = module.toLowerCase();

            // Create a script tag
            let scriptElement = document.createElement("script");

            // Prepare the script tag
            scriptElement.id = "module:" + module;
            scriptElement.src = this.locate(module);

            // Hook to state handlers
            scriptElement.addEventListener("load", () => {
                // Resolve promise
                resolve("Module was loaded");
            });

            scriptElement.addEventListener("error", () => {
                // Remove element
                document.head.removeChild(scriptElement);

                // Reject promise
                reject("Module was not loaded");
            });

            // Make sure the script is not loaded already
            if (document.getElementById(scriptElement.id)) {
                // Resolve promise
                resolve("Module was loaded already");

                // Finish execution
                return;
            }
            // Append script to head
            document.head.appendChild(scriptElement);
        });
    }

    /**
     * Locates a module's script.
     * @param module Module
     */
    static locate(module) {
        // Initialize name & default sources
        let name = module.toLowerCase();
        let repository = "internal";

        // Slice name and look for repository
        let slices = name.split(":");

        // Make sure there are exactly two slices
        if (slices.length === 2) {
            // Update name
            name = slices.pop();
            // Update sources
            repository = slices.pop();
        }

        // Query repository element
        let element = document.querySelector(`meta[name="repository-${repository}"]`);

        // Make sure repository exists
        if (element !== null)
            return `${element.content}/${name}.js`;

        // Return null
        return null;
    }
}