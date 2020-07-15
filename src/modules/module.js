/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

// Initialize attributes
const MODULE_ATTRIBUTE_ID = "id";
const MODULE_ATTRIBUTE_TYPE = "type";
const MODULE_ATTRIBUTE_SOURCE = "src";
const MODULE_ATTRIBUTE_RESOURCES = "resources";

// Initialize tag prefix
const MODULE_PREFIX_TAG = "module:";

// Initialize sources
const MODULE_SOURCES_LOCAL = "local";
const MODULE_SOURCES_GLOBAL = "global";
const MODULE_SOURCES_TEMPLATE = "template";

// Initialize sources object
const MODULE_SOURCES = {
    [MODULE_SOURCES_LOCAL]: "modules",
    [MODULE_SOURCES_GLOBAL]: "https://nadavtasher.github.io/Modules/src/modules",
    [MODULE_SOURCES_TEMPLATE]: "https://nadavtasher.github.io/Template/src/modules"
};

// Initialize default sources
const MODULE_SOURCES_DEFAULT = MODULE_SOURCES_LOCAL;

class Module {
    /**
     * Loads modules.
     * @param module Module
     */
    static load(module) {
        if (arguments.length > 1) {
            return Promise.all(Array.from(arguments).map((module) => Module.load(module)));
        } else {
            return new Promise((resolve, reject) => {
                // Create a script tag
                let scriptElement = document.createElement("script");
                // Transform name
                let moduleName = module.toLowerCase();
                // Initialize default sources
                let moduleSources = MODULE_SOURCES_DEFAULT;
                // Switch sources
                for (let sources in MODULE_SOURCES) {
                    // Append to prefix
                    let prefix = sources + ":";
                    // Check prefix existence
                    if (moduleName.startsWith(prefix)) {
                        // Update module name
                        moduleName = moduleName.slice(prefix.length);
                        // Update module sources
                        moduleSources = sources;
                        // Break loop
                        break;
                    }
                }
                // Prepare the script tag
                scriptElement.setAttribute(MODULE_ATTRIBUTE_TYPE, "text/javascript");
                scriptElement.setAttribute(MODULE_ATTRIBUTE_ID, MODULE_PREFIX_TAG + moduleName);
                scriptElement.setAttribute(MODULE_ATTRIBUTE_SOURCE, MODULE_SOURCES[moduleSources] + "/" + moduleName + "/module.js");
                scriptElement.setAttribute(MODULE_ATTRIBUTE_RESOURCES, MODULE_SOURCES[moduleSources] + "/" + moduleName + "/resources");
                // Hook to state handlers
                scriptElement.addEventListener("load", function () {
                    // Resolve promise
                    resolve("Module was loaded");
                });
                scriptElement.addEventListener("error", function () {
                    // Remove element
                    document.head.removeChild(scriptElement);
                    // Reject promise
                    reject("Module was not loaded");
                });
                // Make sure the module isn't loaded
                for (let i = 0; i < document.scripts.length; i++) {
                    if (document.getElementById(MODULE_PREFIX_TAG + moduleName) !== null) {
                        resolve("Module was already loaded");
                        // Return
                        return;
                    }
                }
                // Append to head
                document.head.appendChild(scriptElement);
            });
        }
    }

    /**
     * Loads a resource.
     * @param module Module
     * @param name Name
     */
    static resource(module, name) {
        return new Promise((resolve, reject) => {
            // Fetch resource directory
            let resourcesURL = document.getElementById(MODULE_PREFIX_TAG + module.toLowerCase()).getAttribute(MODULE_ATTRIBUTE_RESOURCES);
            // Fetch resource
            fetch(resourcesURL + "/" + name).then(response => {
                response.text().then(contents => {
                    // Resolve
                    resolve(contents);
                }).catch(reject);
            }).catch(reject);
        });
    }
}