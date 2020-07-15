/**
 * Copyright (c) 2019 Nadav Tasher
 * https://github.com/NadavTasher/Template/
 **/

class API {
    /**
     * Sends an API call.
     * @param endpoint API to call
     * @param action Action
     * @param parameters Parameters
     * @return Promise
     */
    static call(endpoint = null, action = null, parameters = null) {
        return new Promise((resolve, reject) => {
            // Create a form
            let form = new FormData();
            // Append parameters to form
            for (let key in parameters) {
                if (parameters.hasOwnProperty(key))
                    form.append(key, parameters[key]);
            }
            // Perform the request
            fetch("apis/" + endpoint + "/" + "?" + action, {
                method: "post",
                body: form
            }).then(response => response.text().then((result) => {
                // Try to parse the result as JSON
                try {
                    let API = JSON.parse(result);
                    // Check the result's integrity
                    if (API.hasOwnProperty("status") && API.hasOwnProperty("result")) {
                        // Call the callback with the result
                        if (API["status"] === true) {
                            resolve(API["result"]);
                        } else {
                            reject(API["result"]);
                        }
                    } else {
                        // Call the callback with an error
                        reject("API response malformed");
                    }
                } catch (ignored) {
                    // Call the callback with an error
                    reject("API response malformed");
                }
            }));
        });
    }
}